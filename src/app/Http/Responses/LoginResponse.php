<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user && $user->role === 'admin') {
            return redirect()->intended('/admin/attendance/list');
        }

        if ($user && is_null($user->email_verified_at) && !session()->has('verify_prompt_shown')) {
            session()->put('verify_prompt_shown', true);
            return redirect()->route('verification.notice');
        }

        return redirect()->intended('/attendance');
    }
}
