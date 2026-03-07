<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffTest extends TestCase
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

    /** @testdox 管理者がスタッフ一覧で全一般ユーザーの氏名とメールアドレスを確認できる */
    public function test_admin_can_see_all_staff_names_and_emails(): void
    {
        $admin = $this->createAdmin();
        $u1 = $this->createUser('山田 太郎', 'taro@example.com');
        $u2 = $this->createUser('西 侑奈', 'reina@example.com');

        // 管理者自身は一覧対象外の想定
        User::create([
            'name' => '別管理者',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertOk();
        $response->assertSee($u1->name);
        $response->assertSee($u1->email);
        $response->assertSee($u2->name);
        $response->assertSee($u2->email);
        $response->assertDontSee('admin2@example.com');
    }

    /** @testdox 選択したユーザーの勤怠一覧ページで勤怠情報が正しく表示される */
    public function test_selected_staff_attendance_is_displayed_correctly(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createUser('山田 太郎', 'taro@example.com');

        Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2023-06');

        $response->assertOk();
        $response->assertSee('山田 太郎');
        $response->assertSee('06/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @testdox 前月を押下した時に前月の情報が表示される */
    public function test_previous_month_attendance_is_displayed_for_staff(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createUser('山田 太郎', 'taro@example.com');

        Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2023-05-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2023-06-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2023-05');

        $response->assertOk();
        $response->assertSee('2023/05');
        $response->assertSee('05/10');
        $response->assertDontSee('06/10');
    }

    /** @testdox 翌月を押下した時に翌月の情報が表示される */
    public function test_next_month_attendance_is_displayed_for_staff(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createUser('山田 太郎', 'taro@example.com');

        Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2023-06-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2023-07-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2023-07');

        $response->assertOk();
        $response->assertSee('2023/07');
        $response->assertSee('07/10');
        $response->assertDontSee('06/10');
    }

    /** @testdox 詳細を押下すると管理者の勤怠詳細画面に遷移できる */
    public function test_detail_link_navigates_to_admin_attendance_detail_page(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createUser('山田 太郎', 'taro@example.com');

        $attendance = Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2023-06');

        $response->assertOk();
        $response->assertSee('/admin/attendance/' . $attendance->id);

        $this->actingAs($admin)
            ->get('/admin/attendance/' . $attendance->id)
            ->assertOk();
    }
}
