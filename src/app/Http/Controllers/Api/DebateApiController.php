<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ChatHistory;

class DebateApiController extends Controller
{
    public function getAiResponse(Request $request)
    {
        try {
            $userMessage = $request->input('message');

            if (empty($userMessage)) {
                return response()->json(['response' => 'メッセージが空です。'], 400);
            }

            // ✅ セッションIDを取得
            $sessionId = session()->getId();

            // ✅ チャット履歴を取得、なければ作成
            $chatHistory = ChatHistory::firstOrCreate(
                ['session_id' => $sessionId],
                ['messages' => []]
            );

            $messages = $chatHistory->messages;

            // ✅ ユーザーのメッセージを追加
            $messages[] = ['role' => 'user', 'content' => $userMessage];

            // ✅ OpenAI API へ送信
            $apiKey = env('CHAT_GPT_API_KEY');
            $url = 'https://api.openai.com/v1/chat/completions';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'model' => 'gpt-4o',
                'messages' => $messages,
                'temperature' => 0.9,
                'max_tokens' => 1500,
            ]);

            if ($response->successful()) {
                $aiMessage = $response->json('choices.0.message.content');

                // ✅ AIのメッセージを履歴に追加
                $messages[] = ['role' => 'assistant', 'content' => $aiMessage];

                // ✅ データベースに履歴を更新
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

    public function getChatHistory()
    {
        try {
            $sessionId = session()->getId();
            Log::info("Fetching chat history for session: " . $sessionId);

            $chatHistory = ChatHistory::where('session_id', $sessionId)->first();

            if (!$chatHistory) {
                Log::info("No chat history found for session: " . $sessionId);
                return response()->json(['history' => []]);
            }

            return response()->json(['history' => $chatHistory->messages]);
        } catch (\Exception $e) {
            Log::error("履歴取得エラー: " . $e->getMessage());
            return response()->json(['error' => '履歴の取得中にエラーが発生しました。'], 500);
        }
    }

    public function resetChatHistory()
    {
        try {
            $sessionId = session()->getId();
            ChatHistory::where('session_id', $sessionId)->delete();

            return response()->json(['message' => 'ディベートの履歴をリセットしました。']);
        } catch (\Exception $e) {
            Log::error("履歴リセットエラー: " . $e->getMessage());
            return response()->json(['error' => '履歴のリセットに失敗しました。'], 500);
        }
    }
}
