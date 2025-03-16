<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * Attempt to authenticate a user.
     *
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function login(string $email, string $password): ?array
    {
        $credentials = ['email' => $email, 'password' => $password];

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return null;
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated user.
     *
     * @return User|null
     */
    public function me(): ?User
    {
        return Auth::guard('api')->user();
    }

    /**
     * Log the user out (invalidate the token).
     *
     * @return bool
     */
    public function logout(): bool
    {
        Auth::guard('api')->logout();
        return true;
    }

    /**
     * Refresh a token.
     *
     * @return array
     */
    public function refreshToken(): array
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     * @return array
     */
    protected function respondWithToken(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user(),
        ];
    }
}
