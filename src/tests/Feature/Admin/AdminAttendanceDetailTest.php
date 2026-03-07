<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $admin->forceFill(['email_verified_at' => now()])->save();

        return $admin;
    }

    private function createUser(): User
    {
        $user = User::create([
            'name' => '西 侑奈',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function createAttendanceWithBreak(User $user): Attendance
    {
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
            'note' => '電車遅延のため',
        ]);

        $attendance->breaks()->create([
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        return $attendance;
    }

    private function showPath(Attendance $attendance): string
    {
        return '/admin/attendance/' . $attendance->id;
    }

    private function submitPath(Attendance $attendance): string
    {
        return '/admin/attendance/' . $attendance->id;
    }

    private function validPayload(): array
    {
        return [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'note' => '電車遅延のため',
        ];
    }

    /** @testdox 勤怠詳細画面に表示されるデータが選択したものと一致する */
    public function test_admin_attendance_detail_shows_selected_data(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $this->actingAs($admin)
            ->get($this->showPath($attendance))
            ->assertOk()
            ->assertSee('西 侑奈')
            ->assertSee('2023年')
            ->assertSee('6月1日')
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('12:00')
            ->assertSee('13:00')
            ->assertSee('電車遅延のため');
    }

    /** @testdox 出勤時間が退勤時間より後の場合、エラーメッセージが表示される */
    public function test_validation_error_when_clock_in_is_after_clock_out(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $payload = $this->validPayload();
        $payload['clock_in'] = '19:00';
        $payload['clock_out'] = '18:00';

        $response = $this->actingAs($admin)
            ->from($this->showPath($attendance))
            ->post($this->submitPath($attendance), $payload);

        $response->assertRedirect($this->showPath($attendance));
        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @testdox 休憩開始時間が退勤時間より後の場合、エラーメッセージが表示される */
    public function test_validation_error_when_break_start_is_after_clock_out(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $payload = $this->validPayload();
        $payload['breaks'][0]['start'] = '19:00';
        $payload['breaks'][0]['end'] = '19:30';

        $response = $this->actingAs($admin)
            ->from($this->showPath($attendance))
            ->post($this->submitPath($attendance), $payload);

        $response->assertRedirect($this->showPath($attendance));
        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @testdox 休憩終了時間が退勤時間より後の場合、エラーメッセージが表示される */
    public function test_validation_error_when_break_end_is_after_clock_out(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $payload = $this->validPayload();
        $payload['breaks'][0]['start'] = '17:30';
        $payload['breaks'][0]['end'] = '18:30';

        $response = $this->actingAs($admin)
            ->from($this->showPath($attendance))
            ->post($this->submitPath($attendance), $payload);

        $response->assertRedirect($this->showPath($attendance));
        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @testdox 備考欄が未入力の場合、エラーメッセージが表示される */
    public function test_validation_error_when_note_is_empty(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $payload = $this->validPayload();
        $payload['note'] = '';

        $response = $this->actingAs($admin)
            ->from($this->showPath($attendance))
            ->post($this->submitPath($attendance), $payload);

        $response->assertRedirect($this->showPath($attendance));
        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
