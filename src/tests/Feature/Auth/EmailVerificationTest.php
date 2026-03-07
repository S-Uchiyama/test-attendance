<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @testdox 会員登録後に認証メールが送信される */
    public function test_verification_email_is_sent_after_registration(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $user = User::where('email', 'user@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @testdox メール認証誘導画面で認証ボタンのリンク先が表示される */
    public function test_verification_notice_page_has_verify_link_button(): void
    {
        $user = User::create([
            'name' => '未認証ユーザー',
            'email' => 'unverified@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->get('/email/verify')
            ->assertOk()
            ->assertSee('認証はこちらから')
            ->assertSee('http://localhost:8025');
    }

    /** @testdox メール認証を完了すると勤怠登録画面へ遷移する */
    public function test_user_is_redirected_to_attendance_after_email_verification(): void
    {
        $user = User::create([
            'name' => '未認証ユーザー',
            'email' => 'unverified@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $this->actingAs($user)
            ->get($verifyUrl)
            ->assertRedirect('/attendance?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
