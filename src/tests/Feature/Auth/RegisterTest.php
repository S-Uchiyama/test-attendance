<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @testdox 名前が未入力の場合「お名前を入力してください」を表示する */
    public function test_name_is_required_for_registration(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /** @testdox メールアドレスが未入力の場合「メールアドレスを入力してください」を表示する */
    public function test_email_is_required_for_registration(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @testdox パスワードが8文字未満の場合「パスワードは8文字以上で入力してください」を表示する */
    public function test_password_must_be_at_least_8_characters_for_registration(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'user@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /** @testdox パスワード確認が一致しない場合「パスワードと一致しません」を表示する */
    public function test_password_confirmation_must_match_for_registration(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    /** @testdox パスワードが未入力の場合「パスワードを入力してください」を表示する */
    public function test_password_is_required_for_registration(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'user@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @testdox 正しい入力値の場合はユーザー情報が保存される */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'user@example.com',
            'role' => 'user',
        ]);

        $user = \App\Models\User::where('email', 'user@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
