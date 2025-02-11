<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebateController extends Controller
{
    // 議論画面を表示
    public function index()
    {
        return view('debate');
    }
}
