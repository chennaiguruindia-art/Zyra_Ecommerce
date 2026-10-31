<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken()
            ?: $request->header('X-API-TOKEN')
            ?: $request->input('api_token');

        if ($token) {
            $user = User::where('remember_token', $token)->first();
            if ($user) {
                Auth::setUser($user);
                $request->setUserResolver(fn () => $user);
                return $next($request);
            }
        }

        if (Auth::check()) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated. Valid Bearer token required.',
        ], 401);
    }
}
