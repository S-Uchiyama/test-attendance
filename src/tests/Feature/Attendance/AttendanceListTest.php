<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
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

    /** @testdox 勤怠一覧に自分が行った勤怠情報が全て表示される */
    public function test_attendance_list_shows_only_own_records(): void
    {
        $me = $this->createVerifiedUser('me@example.com', '自分');
        $other = $this->createVerifiedUser('other@example.com', '他人');

        Attendance::create([
            'user_id' => $me->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $me->id,
            'work_date' => '2023-06-02',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $other->id,
            'work_date' => '2023-06-03',
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($me)->get('/attendance/list?month=2023-06');

        $response->assertOk();
        $response->assertSee('06/01');
        $response->assertSee('06/02');
        $response->assertDontSee('06/03');
    }

    /** @testdox 勤怠一覧画面遷移時に現在の月が表示される */
    public function test_current_month_is_displayed_on_attendance_list(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get('/attendance/list?month=2023-06');

        $response->assertOk();
        $response->assertSee('2023/06');
    }

    /** @testdox 前月を押下した時に前月の情報が表示される */
    public function test_previous_month_records_are_displayed(): void
    {
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-05-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2023-05');

        $response->assertOk();
        $response->assertSee('2023/05');
        $response->assertSee('05/10');
        $response->assertDontSee('06/10');
    }

    /** @testdox 翌月を押下した時に翌月の情報が表示される */
    public function test_next_month_records_are_displayed(): void
    {
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-07-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2023-07');

        $response->assertOk();
        $response->assertSee('2023/07');
        $response->assertSee('07/10');
        $response->assertDontSee('06/10');
    }

    /** @testdox 詳細を押下するとその日の勤怠詳細画面に遷移できる */
    public function test_detail_link_navigates_to_attendance_detail_page(): void
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2023-06');

        $response->assertOk();
        $response->assertSee('/attendance/detail/' . $attendance->id);

        $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id)
            ->assertOk();
    }
}
