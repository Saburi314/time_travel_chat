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
    
        \Log::info('🔍 取得した userToken:', [
            'userToken' => $userToken,
            'length' => $userToken ? strlen($userToken) : 'null'
        ]);
    
        if (!$userToken) {
            $userToken = 'user_' . \Illuminate\Support\Str::random(10);
            \Log::info('🆕 新しく生成された userToken:', [
                'userToken' => $userToken,
                'length' => strlen($userToken)
            ]);
    
            cookie()->queue(cookie('user_token', $userToken, 60 * 24 * 365));
        }
    
        return $userToken;
    }
    
}
