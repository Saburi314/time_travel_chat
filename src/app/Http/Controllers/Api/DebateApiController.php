<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ChatHistoryService;
use App\Services\UserTokenService;
use App\Services\AiService;
use App\Models\Opponent;

class DebateApiController extends Controller
{
    private ChatHistoryService $chatHistoryService;
    private UserTokenService $userTokenService;
    private AiService $aiService;

    public function __construct(
        ChatHistoryService $chatHistoryService,
        UserTokenService $userTokenService,
        AiService $aiService
    ) {
        $this->chatHistoryService = $chatHistoryService;
        $this->userTokenService = $userTokenService;
        $this->aiService = $aiService;
    }

    /**
     * 🔹 AIのレスポンスを取得
     */
    public function getAiResponse(Request $request)
    {
        $userToken = $this->userTokenService->getUserToken();
        $opponent = Opponent::getOpponent((int) $request->input('opponentId'));

        $aiMessage = $this->chatHistoryService->handleChatMessage(
            $userToken, 
            $opponent->id, 
            $request->input('message')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'AIのレスポンスを取得しました。',
            'data' => ['response' => $aiMessage],
        ]);
    }

    /**
     * 🔹 チャット履歴を取得
     */
    public function getChatHistory(Request $request)
    {
        $userToken = $this->userTokenService->getUserToken();
        $opponent = Opponent::getOpponent((int) $request->query('opponentId'));

        $chatHistory = ChatHistory::getChatHistory($userToken, $opponent->id);

        return response()->json([
            'status' => 'success',
            'message' => 'チャット履歴を取得しました。',
            'data' => ['history' => $chatHistory->messages ?? []],
        ]);
    }

    /**
     * 🔹 チャット履歴を削除
     */
    public function deleteChatHistory(Request $request)
    {
        $userToken = $this->userTokenService->getUserToken();
        $opponent = Opponent::getOpponent((int) $request->input('opponentId'));

        ChatHistory::deleteChatHistory($userToken, $opponent->id);

        return response()->json([
            'status' => 'success',
            'message' => 'ディベートの履歴をリセットしました。',
            'data' => [
                'csrf_token' => csrf_token(),
            ],
        ]);
    }
}
