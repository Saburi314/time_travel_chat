<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ChatHistoryService;
use App\Services\UserTokenService;
use App\Models\Opponent;
use App\Models\ChatHistory;
use Illuminate\Support\Facades\Log;

class DebateApiController extends Controller
{
    private ChatHistoryService $chatHistoryService;
    private UserTokenService $userTokenService;

    public function __construct(
        ChatHistoryService $chatHistoryService,
        UserTokenService $userTokenService
    ) {
        $this->chatHistoryService = $chatHistoryService;
        $this->userTokenService = $userTokenService;
    }

    /**
     * 🔹 AIのレスポンスを取得
     */
    public function getAiResponse(Request $request)
    {
        try {
            $validated = $request->validate([
                'opponentId' => 'required|integer',
                'message' => 'nullable|string',
            ]);
    
            $userToken = $this->userTokenService->getUserToken();
            $opponentId = $validated['opponentId'];
            $userMessage = $validated['message'] ?? '';
            $opponent = Opponent::getOpponent($opponentId);
            
            if (!$opponent) {
                return response()->json(['status' => 'error', 'message' => 'Opponent not found'], 400);
            }
    
            $aiMessage = $this->chatHistoryService->handleChatMessage($userToken, $opponent->id, $userMessage);
    
            return response()->json([
                'status' => 'success',
                'message' => 'AIのレスポンスを取得しました。',
                'data' => ['response' => $aiMessage],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'AIのレスポンス取得時にエラーが発生しました。',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 🔹 チャット履歴を取得
     */
    public function getChatHistory(Request $request)
    {
        try {
            $validated = $request->validate([
                'opponentId' => 'required|integer',
            ]);

            $userToken = $this->userTokenService->getUserToken();
            $opponentId = $validated['opponentId'];
            $opponent = Opponent::getOpponent($opponentId);
    
            if (!$opponent) {
                return response()->json(['status' => 'error', 'message' => 'Opponent not found'], 400);
            }
    
            $chatHistory = ChatHistory::getChatHistory($userToken, $opponent->id);
    
            return response()->json([
                'status' => 'success',
                'message' => 'チャット履歴を取得しました。',
                'data' => ['history' => $chatHistory->messages ?? []],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'チャット履歴の取得中にエラーが発生しました。',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 🔹 チャット履歴を削除
     */
    public function deleteChatHistory(Request $request)
    {
        try {
            $validated = $request->validate([
                'opponentId' => 'required|integer',
            ]);

            $userToken = $this->userTokenService->getUserToken();
            $opponentId = $validated['opponentId'];
            $opponent = Opponent::getOpponent($opponentId);

            if (!$opponent) {
                return response()->json(['status' => 'error', 'message' => 'Opponent not found'], 400);
            }

            ChatHistory::deleteChatHistory($userToken, $opponent->id);

            return response()->json([
                'status' => 'success',
                'message' => '会話の履歴をリセットしました。',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'チャット履歴の削除中にエラーが発生しました。',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
