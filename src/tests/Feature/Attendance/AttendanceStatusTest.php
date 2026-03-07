<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
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

    /** @testdox 勤務外の場合、勤怠ステータスが「勤務外」と表示される */
    public function test_status_off_is_displayed(): void
    {
        Carbon::setTestNow('2023-06-01 09:00:00');
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();
        $response->assertSee('勤務外');

        Carbon::setTestNow();
    }

    /** @testdox 出勤中の場合、勤怠ステータスが「出勤中」と表示される */
    public function test_status_working_is_displayed(): void
    {
        Carbon::setTestNow('2023-06-01 09:00:00');
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    /** @testdox 休憩中の場合、勤怠ステータスが「休憩中」と表示される */
    public function test_status_on_break_is_displayed(): void
    {
        Carbon::setTestNow('2023-06-01 12:00:00');
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'status' => 'working',
        ]);

        $attendance->breaks()->create([
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    /** @testdox 退勤済の場合、勤怠ステータスが「退勤済」と表示される */
    public function test_status_done_is_displayed(): void
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

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }
}
