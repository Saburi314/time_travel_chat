<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebateController extends Controller
{
    public function index(Request $request)
    {
        $opponentKey = $request->query('opponentKey', 'hiroyuki');

        return view('debate', compact('opponentKey'));
    }
}
