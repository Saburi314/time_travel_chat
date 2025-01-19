<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebateController extends Controller
{
    // ホーム画面を表示
    public function home()
    {
        return view('home');
    }

    // 議論画面を表示
    public function debate()
    {
        return view('debate');
    }
}
