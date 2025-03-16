<?php

namespace Tests\Traits;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

trait WithJwtAuth
{
    /**
     * Act as a user with JWT token
     *
     * @param User $user
     * @return $this
     */
    public function actingAsWithJwt(User $user)
    {
        try {
            $token = JWTAuth::fromUser($user);
            $this->withHeader('Authorization', "Bearer {$token}");
            $this->withHeader('Accept', 'application/json');
        } catch (\Exception $e) {
            // If JWT generation fails, fall back to Laravel's built-in auth for testing
            $this->actingAs($user);
        }

        return $this;
    }
}
