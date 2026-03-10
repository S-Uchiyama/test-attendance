<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse;

class LoginController extends Controller
{
    public function __construct(private StatefulGuard $guard)
    {
    }

    public function store(LoginUserRequest $request): LoginResponse
    {
        $user = User::where('email', $request->input('email'))
            ->where('role', 'user')
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $this->guard->login($user);
        $request->session()->regenerate();

        return app(LoginResponse::class);
    }
}
