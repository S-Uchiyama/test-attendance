<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;

class RegisterController extends Controller
{
    public function __construct(private StatefulGuard $guard)
    {
    }

    public function store(RegisterUserRequest $request, CreatesNewUsers $creator): RegisterResponse
    {
        $input = $request->validated();

        if (config('fortify.lowercase_usernames')) {
            $input[Fortify::username()] = Str::lower($input[Fortify::username()]);
        }

        event(new Registered($user = $creator->create($input)));

        $this->guard->login($user);

        return app(RegisterResponse::class);
    }
}
