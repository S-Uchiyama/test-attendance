<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
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

    /** @testdox 勤務外ユーザーは出勤ボタン表示後に出勤処理で勤務中になる */
    public function test_clock_in_button_works_and_status_changes_to_working(): void
    {
        Carbon::setTestNow('2023-06-01 09:00:00');
        $user = $this->createVerifiedUser();

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('出勤');

        $this->actingAs($user)
            ->post('/attendance/clock-in')
            ->assertRedirect('/attendance');

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'status' => 'working',
            'clock_in' => '09:00:00',
        ]);

        Carbon::setTestNow();
    }

    /** @testdox 退勤済ユーザーは同日に出勤ボタンが表示されない */
    public function test_clock_in_button_is_not_shown_after_done_status(): void
    {
        Carbon::setTestNow('2023-06-01 18:00:00');
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertDontSee('出勤');

        Carbon::setTestNow();
    }

    /** @testdox 出勤時刻が勤怠一覧画面で確認できる */
    public function test_clock_in_time_is_visible_on_attendance_list(): void
    {
        Carbon::setTestNow('2023-06-01 09:00:00');
        $user = $this->createVerifiedUser();

        $this->actingAs($user)
            ->post('/attendance/clock-in')
            ->assertRedirect('/attendance');

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertOk()
            ->assertSee('06/01(木)')
            ->assertSee('09:00');

        Carbon::setTestNow();
    }
}
