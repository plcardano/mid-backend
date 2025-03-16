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
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', "Bearer {$token}");
        $this->withHeader('Accept', 'application/json');

        return $this;
    }
}
