<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Constants\Opponents;

class DebateController extends Controller
{
    public function index(Request $request)
    {
        $opponentKey = $request->query('opponentKey', Opponents::DEFAULT);

        return view('debate', compact('opponentKey'));
    }
}
