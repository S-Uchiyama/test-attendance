<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(string $email = 'user@example.com', string $name = '一般ユーザー'): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function createAttendance(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
            'note' => '通常勤務',
        ]);
    }

    private function submitPath(Attendance $attendance): string
    {
        return route('attendance.request.store', ['id' => $attendance->id]);
    }

    private function validPayload(): array
    {
        return [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'reason' => '電車遅延のため',
        ];
    }

    /** @testdox 出勤時間が退勤時間より後の場合、エラーメッセージが表示される */
    public function test_validation_error_when_clock_in_is_after_clock_out(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $payload = $this->validPayload();
        $payload['clock_in'] = '19:00';
        $payload['clock_out'] = '18:00';

        $response = $this->actingAs($user)
            ->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post($this->submitPath($attendance), $payload);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @testdox 休憩開始が退勤より後の場合、エラーメッセージが表示される */
    public function test_validation_error_when_break_start_is_after_clock_out(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $payload = $this->validPayload();
        $payload['breaks'][0]['start'] = '19:00';
        $payload['breaks'][0]['end'] = '19:30';

        $response = $this->actingAs($user)
            ->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post($this->submitPath($attendance), $payload);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);
        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @testdox 休憩終了が退勤より後の場合、エラーメッセージが表示される */
    public function test_validation_error_when_break_end_is_after_clock_out(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $payload = $this->validPayload();
        $payload['breaks'][0]['start'] = '17:30';
        $payload['breaks'][0]['end'] = '18:30';

        $response = $this->actingAs($user)
            ->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post($this->submitPath($attendance), $payload);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);
        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @testdox 備考欄が未入力の場合、エラーメッセージが表示される */
    public function test_validation_error_when_reason_is_empty(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $payload = $this->validPayload();
        $payload['reason'] = '';

        $response = $this->actingAs($user)
            ->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post($this->submitPath($attendance), $payload);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);
        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

    /** @testdox 修正申請を実行すると申請データが保存される */
    public function test_correction_request_is_created(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $this->actingAs($user)->post($this->submitPath($attendance), $this->validPayload());

        $this->assertDatabaseHas('attendance_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'reason' => '電車遅延のため',
        ]);
    }

    /** @testdox 申請一覧の承認待ちタブに自分の申請が表示される */
    public function test_pending_requests_are_shown_in_pending_tab(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        DB::table('attendance_correction_requests')->insert([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'target_date' => '2023-06-01',
            'requested_clock_in' => '09:30:00',
            'requested_clock_out' => '18:30:00',
            'reason' => '電車遅延のため',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=pending')
            ->assertOk()
            ->assertSee('承認待ち')
            ->assertSee('電車遅延のため');
    }

    /** @testdox 申請一覧の承認済みタブに承認済み申請が表示される */
    public function test_approved_requests_are_shown_in_approved_tab(): void
    {
        $user = $this->createVerifiedUser();
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $attendance = $this->createAttendance($user);

        DB::table('attendance_correction_requests')->insert([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'target_date' => '2023-06-01',
            'requested_clock_in' => '09:30:00',
            'requested_clock_out' => '18:30:00',
            'reason' => '病院受診のため',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=approved')
            ->assertOk()
            ->assertSee('承認済み')
            ->assertSee('病院受診のため');
    }

    /** @testdox 申請一覧の詳細リンクから勤怠詳細画面へ遷移できる */
    public function test_detail_link_in_request_list_navigates_to_attendance_detail(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        DB::table('attendance_correction_requests')->insert([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'target_date' => '2023-06-01',
            'requested_clock_in' => '09:30:00',
            'requested_clock_out' => '18:30:00',
            'reason' => '私用のため',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=pending')
            ->assertOk()
            ->assertSee('/attendance/detail/' . $attendance->id);

        $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id)
            ->assertOk();
    }
}
