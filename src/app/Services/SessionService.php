<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class SessionService
{
    /**
     * 🔹 セッションの無効化とトークンの再生成
     */
    public function invalidateSession(): void
    {
        Session::invalidate();
        Session::regenerateToken();
    }
}
