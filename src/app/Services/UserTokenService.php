<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class UserTokenService
{
    /**
     * 🔹 `user_token` を取得 or 生成
     */
    public function getUserToken(Request $request): string
    {
        $userToken = $request->cookie('user_token');

        if (!$userToken) {
            $userToken = 'user_' . Str::random(10);
            $this->setUserToken($userToken);
        }

        return $userToken;
    }

    /**
     * 🔹 `user_token` を Cookie に保存
     */
    public function setUserToken(string $userToken): void
    {
        // 1年 (365日) 有効な Cookie
        Cookie::queue(cookie('user_token', $userToken, 60 * 24 * 365));
    }
}
