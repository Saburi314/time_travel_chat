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
            //  バリデーション: `message` キーが存在し、かつ文字列であることを確認
            $request->validate([
                'message' => 'required|string'
            ]);
    
            $userMessage = $request->input('message');
    
            $sessionId = session()->getId();
    
            //  履歴を取得または新規作成
            $chatHistory = ChatHistory::firstOrCreate(
                ['session_id' => $sessionId],
                ['messages' => []]
            );
    
            $messages = $chatHistory->messages;
    
            //  ユーザーのメッセージを追加
            $messages[] = ['role' => 'user', 'content' => $userMessage];
    
            //  OpenAI API へ送信
            $apiKey = env('CHAT_GPT_API_KEY');
            $url = 'https://api.openai.com/v1/chat/completions';
    
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'model' => 'gpt-4o',
                'messages' => array_merge([
                    [
                        'role' => 'system',
                        'content' => "あなたは **西村博之** です。\n"
                        . "揚げ足取りと煽るのが得意で、議論相手を小馬鹿にしながらも、的確な指摘を行います。\n"
                        . "まずはユーザーとディベートを行う議題を決めます。\n"
                        . "ユーザーの意見には真っ向から反対し、論理的に相手を追い詰めながらも、冗談を交えて返答してください。\n"
                        . "ユーザーが「終了」と言ったら、その時点までの議論を公平な立場から判定する。\n"
                        . "勝者は `### 🏆 勝者: [名前]` のように **Markdown の見出し形式** で必ず表示する。\n"
                        . "また、その後に理由を詳しく説明する。\n"
                    ]
                ], $messages),
                'temperature' => 1.0,
                'max_tokens' => 1000,
            ]);
    
            if ($response->successful()) {
                $aiMessage = $response->json('choices.0.message.content');
    
                //  AIのメッセージを履歴に追加
                $messages[] = ['role' => 'assistant', 'content' => $aiMessage];
    
                //  データベースに履歴を更新
                $chatHistory->update(['messages' => $messages]);
    
                return response()->json(['response' => $aiMessage]);
            } else {
                Log::error("AI API エラー", ['error' => $response->json()]);
                return response()->json(['response' => 'AIとの通信でエラーが発生しました。'], $response->status());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['response' => 'リクエストが不正です。'], 400);
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

            $chatHistory = ChatHistory::getHistoryBySession($sessionId);

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
            // 🔹 セッションを新しく生成
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
