<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
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

    private function createUser(string $email, string $name): User
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

    /** @testdox 管理者の勤怠一覧で当日の全ユーザー勤怠情報が正確に表示される */
    public function test_admin_can_see_all_users_attendance_of_the_day(): void
    {
        Carbon::setTestNow('2023-06-01 10:00:00');

        $admin = $this->createAdmin();
        $user1 = $this->createUser('u1@example.com', '山田 太郎');
        $user2 = $this->createUser('u2@example.com', '西 侑奈');

        Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2023-06-01',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertOk();
        $response->assertSee('山田 太郎');
        $response->assertSee('西 侑奈');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        Carbon::setTestNow();
    }

    /** @testdox 管理者勤怠一覧の初期表示で現在日付が表示される */
    public function test_admin_attendance_list_displays_current_date_on_open(): void
    {
        Carbon::setTestNow('2023-06-01 10:00:00');

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertOk();
        $response->assertSee('2023/06/01');

        Carbon::setTestNow();
    }

    /** @testdox 前日を指定すると前日の日付の勤怠情報が表示される */
    public function test_admin_can_see_previous_day_attendance(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser('u1@example.com', '山田 太郎');

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-05-31',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2023-05-31');

        $response->assertOk();
        $response->assertSee('2023/05/31');
        $response->assertSee('09:00');
        $response->assertDontSee('10:00');
    }

    /** @testdox 翌日を指定すると翌日の日付の勤怠情報が表示される */
    public function test_admin_can_see_next_day_attendance(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser('u1@example.com', '山田 太郎');

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-02',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2023-06-02');

        $response->assertOk();
        $response->assertSee('2023/06/02');
        $response->assertSee('10:00');
        $response->assertDontSee('09:00');
    }
}
