<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ChatHistory;
use App\Constants\Opponents;

class DebateApiController extends Controller
{
    /**
     * 🔹 AIのレスポンスを取得
     */
    public function getAiResponse(Request $request)
    {
        try {
            $userMessage = $request->input('message');
            $opponentKey = $request->input('opponentKey', 'hiroyuki');
            $opponentData = Opponents::get($opponentKey);

            $sessionId = session()->getId();

            // 🔹 履歴を取得または新規作成
            $chatHistory = ChatHistory::firstOrCreate(
                ['session_id' => $sessionId],
                ['messages' => []]
            );

            $messages = $chatHistory->messages;
            $messages[] = ['role' => 'user', 'content' => $userMessage];

            // 🔹 OpenAI API へ送信
            $apiKey = env('CHAT_GPT_API_KEY');
            $url = 'https://api.openai.com/v1/chat/completions';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'model' => 'gpt-4o',
                'messages' => array_merge([['role' => 'system', 'content' => $opponentData['system_message']]], $messages),
                'temperature' => 1.0,
                'max_tokens' => 1000,
            ]);

            if ($response->successful()) {
                $aiMessage = $response->json('choices.0.message.content');

                $messages[] = ['role' => 'assistant', 'content' => $aiMessage];
                $chatHistory->update(['messages' => $messages]);

                return response()->json(['response' => $aiMessage]);
            } else {
                Log::error("AI API エラー", ['error' => $response->json()]);
                return response()->json(['response' => 'AIとの通信でエラーが発生しました。'], $response->status());
            }
        } catch (\Exception $e) {
            Log::error("AI API 通信エラー: " . $e->getMessage());
            return response()->json(['response' => 'サーバーエラーが発生しました。'], 500);
        }
    }

    /**
     * 🔹 チャット履歴を取得
     */
    public function getChatHistory(Request $request)
    {
        try {
            $sessionId = session()->getId();
            $opponentKey = $request->query('opponentKey', 'hiroyuki');

            Log::info("Fetching chat history for session: " . $sessionId);

            $chatHistory = ChatHistory::getHistoryBySession($sessionId);

            if (!$chatHistory) {
                Log::info("No chat history found for session: " . $sessionId);
                return response()->json(['history' => [], 'opponentKey' => $opponentKey]);
            }

            return response()->json(['history' => $chatHistory->messages, 'opponentKey' => $opponentKey]);
        } catch (\Exception $e) {
            Log::error("履歴取得エラー: " . $e->getMessage());
            return response()->json(['error' => '履歴の取得中にエラーが発生しました。'], 500);
        }
    }

    /**
     * 🔹 チャット履歴をリセット
     */
    public function resetChatHistory()
    {
        try {
            session()->invalidate();
            session()->regenerateToken();

            return response()->json([
                'message' => 'ディベートのセッションをリセットしました。',
                'csrf_token' => csrf_token()
            ]);
        } catch (\Exception $e) {
            Log::error("セッションリセットエラー: " . $e->getMessage());
            return response()->json(['error' => 'セッションのリセットに失敗しました。'], 500);
        }
    }
}
