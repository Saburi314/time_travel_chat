<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // セッションをリセット（議論履歴を削除する）
        session()->invalidate();
        session()->regenerateToken();

        // ホーム画面へ遷移
        return view('home');
    }
}
