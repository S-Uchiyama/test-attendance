<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @testdox 勤怠打刻画面で現在日時がUI形式で表示される */
    public function test_attendance_page_displays_current_datetime_in_ui_format(): void
    {
        Carbon::setTestNow('2023-06-01 08:00:00');

        $user = User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // fillableに無い場合でも確実にメール認証済みにする
        $user->forceFill(['email_verified_at' => now()])->save();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();
        $response->assertSee('2023年6月1日(木)');
        $response->assertSee('08:00');

        Carbon::setTestNow();
    }
}
