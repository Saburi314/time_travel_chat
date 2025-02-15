<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opponent;

class DebateController extends Controller
{
    public function index(Request $request)
    {
        // opponentId を取得（無ければデフォルトを適用）
        $opponent = Opponent::getOpponent((int) $request->query('opponent_id'));

        return view('debate', compact('opponent'));
    }
}
