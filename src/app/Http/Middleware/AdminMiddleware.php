<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 管理者以外は管理画面に入れない
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403);
        }

        return $next($request);
    }
}
