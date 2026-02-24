<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Responses\LoginResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(fn () => view('auth.register'));
        Fortify::loginView(function (Request $request) {
            // URLでログイン画面を切り替える
            return $request->is('admin/login')
                ? view('admin.auth.login')
                : view('auth.login');
        });

        Fortify::authenticateUsing(function (Request $request) {
            // 管理者ログインURLなら admin、それ以外は user として判定
            $role = $request->is('admin/login') ? 'admin' : 'user';

            $user = User::where('email', $request->email)
                ->where('role', $role)
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->email.$request->ip());
        });
    }
}
