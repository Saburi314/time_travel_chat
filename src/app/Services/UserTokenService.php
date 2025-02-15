<?php

namespace App\Services;

use App\Models\Opponent;

class UserTokenService
{
    /**
     * 🔹 `user_token` を取得
     */
    public function getUserToken(): string
    {
        $userToken = request()->cookie('user_token');

        if (!$userToken) {
            $userToken = 'user_' . \Illuminate\Support\Str::random(10);
            cookie()->queue(cookie('user_token', $userToken, 60 * 24 * 365));
        }

        return $userToken;
    }
}
