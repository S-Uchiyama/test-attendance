<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
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

    private function createWorkingAttendance(User $user, string $date = '2023-06-01', string $clockIn = '09:00:00'): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date,
            'clock_in' => $clockIn,
            'status' => 'working',
        ]);
    }

    /** @testdox 休憩入ボタンが機能し、処理後にステータスが休憩中になる */
    public function test_break_in_changes_status_to_on_break(): void
    {
        Carbon::setTestNow('2023-06-01 12:00:00');
        $user = $this->createVerifiedUser();
        $this->createWorkingAttendance($user);

        $this->actingAs($user)->get('/attendance')->assertSee('休憩入');

        $this->actingAs($user)
            ->post('/attendance/break-in')
            ->assertRedirect('/attendance');

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('休憩中');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => Attendance::where('user_id', $user->id)->where('work_date', '2023-06-01')->first()->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        Carbon::setTestNow();
    }

    /** @testdox 休憩入と休憩戻を行っても再度休憩入ボタンが表示される */
    public function test_break_can_be_taken_multiple_times_in_a_day(): void
    {
        Carbon::setTestNow('2023-06-01 12:00:00');
        $user = $this->createVerifiedUser();
        $this->createWorkingAttendance($user);

        $this->actingAs($user)->post('/attendance/break-in')->assertRedirect('/attendance');

        Carbon::setTestNow('2023-06-01 12:30:00');
        $this->actingAs($user)->post('/attendance/break-out')->assertRedirect('/attendance');

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('休憩入');

        Carbon::setTestNow();
    }

    /** @testdox 休憩戻ボタンが機能し、処理後にステータスが出勤中になる */
    public function test_break_out_changes_status_back_to_working(): void
    {
        Carbon::setTestNow('2023-06-01 12:00:00');
        $user = $this->createVerifiedUser();
        $attendance = $this->createWorkingAttendance($user);

        $this->actingAs($user)->post('/attendance/break-in')->assertRedirect('/attendance');

        Carbon::setTestNow('2023-06-01 12:30:00');
        $this->actingAs($user)->post('/attendance/break-out')->assertRedirect('/attendance');

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('出勤中');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
        ]);

        Carbon::setTestNow();
    }

    /** @testdox 休憩戻後に再度休憩入した場合、休憩戻ボタンが表示される */
    public function test_break_out_can_be_done_multiple_times_in_a_day(): void
    {
        Carbon::setTestNow('2023-06-01 12:00:00');
        $user = $this->createVerifiedUser();
        $this->createWorkingAttendance($user);

        $this->actingAs($user)->post('/attendance/break-in')->assertRedirect('/attendance');

        Carbon::setTestNow('2023-06-01 12:30:00');
        $this->actingAs($user)->post('/attendance/break-out')->assertRedirect('/attendance');

        Carbon::setTestNow('2023-06-01 15:00:00');
        $this->actingAs($user)->post('/attendance/break-in')->assertRedirect('/attendance');

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('休憩戻');

        Carbon::setTestNow();
    }

    /** @testdox 休憩時刻が勤怠一覧画面で確認できる */
    public function test_break_time_is_visible_on_attendance_list(): void
    {
        Carbon::setTestNow('2023-06-01 09:00:00');
        $user = $this->createVerifiedUser();
        $this->createWorkingAttendance($user);

        Carbon::setTestNow('2023-06-01 12:00:00');
        $this->actingAs($user)->post('/attendance/break-in')->assertRedirect('/attendance');

        Carbon::setTestNow('2023-06-01 13:00:00');
        $this->actingAs($user)->post('/attendance/break-out')->assertRedirect('/attendance');

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertOk()
            ->assertSee('06/01(木)')
            ->assertSee('1:00');

        Carbon::setTestNow();
    }
}
