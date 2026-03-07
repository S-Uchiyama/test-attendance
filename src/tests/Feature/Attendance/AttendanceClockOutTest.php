<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(): User
    {
        $user = User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function createWorkingAttendance(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'status' => 'working',
        ]);
    }

    /** @testdox 退勤ボタンが機能し、処理後にステータスが退勤済になる */
    public function test_clock_out_button_works_and_status_changes_to_done(): void
    {
        Carbon::setTestNow('2023-06-01 18:00:00');
        $user = $this->createVerifiedUser();
        $this->createWorkingAttendance($user);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('退勤');

        $this->actingAs($user)
            ->post('/attendance/clock-out')
            ->assertRedirect('/attendance');

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('退勤済');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'status' => 'done',
            'clock_out' => '18:00:00',
        ]);

        Carbon::setTestNow();
    }

    /** @testdox 退勤時刻が勤怠一覧画面で確認できる */
    public function test_clock_out_time_is_visible_on_attendance_list(): void
    {
        Carbon::setTestNow('2023-06-01 09:00:00');
        $user = $this->createVerifiedUser();
        $this->createWorkingAttendance($user);

        Carbon::setTestNow('2023-06-01 18:00:00');
        $this->actingAs($user)
            ->post('/attendance/clock-out')
            ->assertRedirect('/attendance');

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertOk()
            ->assertSee('06/01(木)')
            ->assertSee('18:00');

        Carbon::setTestNow();
    }
}
