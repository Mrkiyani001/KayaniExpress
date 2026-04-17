<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SingleDeviceLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $payload = Auth::payload();
        $user = Auth::user();
        if($user->remember_token != $payload->get('session_id')){
            Auth::logout();
            return response()->json([
                'message' => 'Logged in from another device. Please login again.',
            ], 401);
        }
        return $next($request);
    }
}
