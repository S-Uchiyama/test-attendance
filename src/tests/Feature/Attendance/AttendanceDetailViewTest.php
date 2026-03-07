<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailViewTest extends TestCase
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

    /** @testdox 勤怠詳細画面の名前がログインユーザーの氏名になっている */
    public function test_detail_page_shows_logged_in_user_name(): void
    {
        $user = $this->createVerifiedUser('user@example.com', '西 侑奈');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
            'note' => '電車遅延のため',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertOk();
        $response->assertSee('西 侑奈');
    }

    /** @testdox 勤怠詳細画面の日付が選択した日付と一致している */
    public function test_detail_page_shows_selected_date(): void
    {
        $user = $this->createVerifiedUser('user@example.com', '西 侑奈');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertOk();
        $response->assertSee('2023年');
        $response->assertSee('6月1日');
    }

    /** @testdox 勤怠詳細画面の出勤退勤時刻が打刻データと一致している */
    public function test_detail_page_shows_clock_in_and_clock_out_times(): void
    {
        $user = $this->createVerifiedUser('user@example.com', '西 侑奈');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertOk();
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @testdox 勤怠詳細画面の休憩時刻が打刻データと一致している */
    public function test_detail_page_shows_break_times(): void
    {
        $user = $this->createVerifiedUser('user@example.com', '西 侑奈');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        $attendance->breaks()->create([
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertOk();
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
