<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Constants\Opponents;
use App\Models\ChatHistory;

class HomeController extends Controller
{
    public function index()
    {
        // セッションリセット（チャット履歴を削除する）
        session()->invalidate();
        session()->regenerateToken();

        return view('home', ['opponents' => Opponents::all()]);
    }
}
