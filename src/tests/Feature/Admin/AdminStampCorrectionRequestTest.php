<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminStampCorrectionRequestTest extends TestCase
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

    private function createUser(string $name, string $email): User
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

    private function createCorrectionRequest(Attendance $attendance, User $user, string $status = 'pending', ?User $approvedBy = null): int
    {
        return DB::table('attendance_correction_requests')->insertGetId([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'target_date' => '2023-06-01',
            'requested_clock_in' => '09:30:00',
            'requested_clock_out' => '18:30:00',
            'reason' => '電車遅延のため',
            'status' => $status,
            'approved_by' => $approvedBy?->id,
            'approved_at' => $approvedBy ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @testdox 承認待ちの修正申請が管理者の承認待ちタブに全て表示される */
    public function test_pending_requests_are_listed_for_admin(): void
    {
        $admin = $this->createAdmin();
        $u1 = $this->createUser('山田 太郎', 'taro@example.com');
        $u2 = $this->createUser('西 侑奈', 'reina@example.com');

        $a1 = $this->createAttendance($u1);
        $a2 = $this->createAttendance($u2);

        $this->createCorrectionRequest($a1, $u1, 'pending');
        $this->createCorrectionRequest($a2, $u2, 'pending');

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list?status=pending');

        $response->assertOk();
        $response->assertSee('承認待ち');
        $response->assertSee('山田 太郎');
        $response->assertSee('西 侑奈');
    }

    /** @testdox 承認済みの修正申請が管理者の承認済みタブに全て表示される */
    public function test_approved_requests_are_listed_for_admin(): void
    {
        $admin = $this->createAdmin();
        $u1 = $this->createUser('山田 太郎', 'taro@example.com');
        $u2 = $this->createUser('西 侑奈', 'reina@example.com');

        $a1 = $this->createAttendance($u1);
        $a2 = $this->createAttendance($u2);

        $this->createCorrectionRequest($a1, $u1, 'approved', $admin);
        $this->createCorrectionRequest($a2, $u2, 'approved', $admin);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list?status=approved');

        $response->assertOk();
        $response->assertSee('承認済み');
        $response->assertSee('山田 太郎');
        $response->assertSee('西 侑奈');
    }

    /** @testdox 修正申請の詳細画面に申請内容が正しく表示される */
    public function test_admin_can_see_request_detail_correctly(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser('西 侑奈', 'reina@example.com');
        $attendance = $this->createAttendance($user);

        $requestId = $this->createCorrectionRequest($attendance, $user, 'pending');

        DB::table('attendance_correction_request_breaks')->insert([
            'attendance_correction_request_id' => $requestId,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/approve/' . $requestId);

        $response->assertOk();
        $response->assertSee('西 侑奈');
        $response->assertSee('2023年');
        $response->assertSee('6月1日');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('電車遅延のため');
    }

    /** @testdox 管理者が承認すると申請が承認済みになり勤怠情報が更新される */
    public function test_admin_approval_updates_request_and_attendance(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser('西 侑奈', 'reina@example.com');
        $attendance = $this->createAttendance($user);

        $requestId = $this->createCorrectionRequest($attendance, $user, 'pending');

        DB::table('attendance_correction_request_breaks')->insert([
            'attendance_correction_request_id' => $requestId,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post('/admin/stamp_correction_request/approve/' . $requestId)
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $requestId,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }
}
