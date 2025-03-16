<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtRenewToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            // Check if token is about to expire (less than 10 minutes)
            $expiration = JWTAuth::factory()->getTTL() * 60;
            $tokenExpiration = Auth::guard('api')->payload()->get('exp');
            $now = now()->timestamp;

            // If token will expire in less than 10 minutes, refresh it
            if ($tokenExpiration - $now < 600) {
                $token = Auth::guard('api')->refresh();

                // Set the new token in the response
                $response = $next($request);
                return $response->header('Authorization', 'Bearer ' . $token);
            }

        } catch (TokenExpiredException $e) {
            // If token is expired, try to refresh it
            try {
                $token = Auth::guard('api')->refresh();
                $request->headers->set('Authorization', 'Bearer ' . $token);

                // Make sure the user is set correctly
                Auth::guard('api')->setToken($token);
                $user = Auth::guard('api')->user();

                $response = $next($request);
                return $response->header('Authorization', 'Bearer ' . $token);

            } catch (JWTException $e) {
                return response()->json(['error' => 'token_invalid'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'token_invalid'], 401);
        }

        return $next($request);
    }
}
