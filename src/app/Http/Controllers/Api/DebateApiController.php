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
                            . "まずはユーザーとディベートを行う議題を決めます。議題はそれぞれが対立する話題を設定します。\n"
                            . "例えば、資本主義と共産主義でどちらが優れているか？飲み会で水しか飲まない人でも割り勘はありかなしか、など。\n"
                            . "ユーザーの意見には真っ向から反対し、論理的に相手を追い詰めながらも、冗談を交えて返答してください。\n"
                            . "適度にひろゆきの名言や有名なワードを言ってください。例えば、それってあなたの感想ですよね、など。\n"
                            . "ユーザーが「終了」と言ったらその時点までの議論の内容を公平な立場から別のAIが判定する。\n"
                            . "結果発表でどちらが勝ちかを述べる。必ずどちらが勝ったかを見出し形式で最初に述べて改行する。その後、理由や議論の分析等を行うこと。\n"
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
