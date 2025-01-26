<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DebateApiController extends Controller
{
    // AI応答を処理
    public function getAiResponse(Request $request)
    {
        $userMessage = $request->input('message');

        // 仮のAI応答（ここを実際のAI連携ロジックに変更）
        $aiResponse = "それについては興味深いですね。「$userMessage」というテーマには深い議論が必要です。";

        return response()->json(['response' => $aiResponse]);
    }
}
