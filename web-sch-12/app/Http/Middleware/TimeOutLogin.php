<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TimeOutLogin
{

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity');
            $now = now();
            $diff = $now->diffInMinutes($lastActivity);

            // Logout jika sesi melebihi 1 menit
            if ($diff >= 20 ) {
                Auth::logout();
                session()->flush(); // Hapus sesi
                return redirect()->route('login-page')->withErrors('Session expired. Please login again.');
            } else {
                session(['last_activity' => $now]);
            }
            // Perbarui waktu aktivitas terakhir
            session(['last_activity' => $now]);
        }
        return $next($request);
    }
}
