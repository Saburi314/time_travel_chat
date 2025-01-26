<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DebateApiController extends Controller
{
    public function getAiResponse(Request $request)
    {
        $userMessage = $request->input('message');

        if (empty($userMessage)) {
            return response()->json(['response' => 'メッセージが空です。'], 400);
        }

        // セッションから履歴を取得（初期化も兼ねる）
        $messages = session('chat_history', [
            ['role' => 'system', 
            'content' => 'あなたは西村博之です。ディベートしたがりで皮肉や煽りを頻繁に行う西村博之になりきってユーザーからの意見を論破して下さい。
            ユーザーとのやり取りでは、ユーモラスで煽り文句を必ず入れてください。
            ユーザーとのディベートをする際に、まずはどのような議題で話すかを決めることから始まります。議題が決まったら議題に沿ってディベートを行います。
            ユーザーが「終了」とコメントしたらその時点でディベートは終わりです。中立の立場にいるAIがどちらが議論に勝ったかを判定し、その理由を述べます。']
        ]);

        // ユーザーのメッセージを履歴に追加
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            // ChatGPT APIキーを取得
            $apiKey = env('CHAT_GPT_API_KEY');

            // ChatGPT APIエンドポイント
            $url = 'https://api.openai.com/v1/chat/completions';

            // APIリクエスト
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'model' => 'gpt-4o', // 使用するモデル
                'messages' => $messages, // 履歴を含める
                'temperature' => 1.0, // 応答の多様性を調整
                // 'max_tokens' => 150, // 応答のトークン数の上限
            ]);

            // APIの応答を取得
            if ($response->successful()) {
                $aiMessage = $response->json('choices.0.message.content');

                // AIのメッセージを履歴に追加
                $messages[] = ['role' => 'assistant', 'content' => $aiMessage];

                // 履歴をセッションに保存
                session(['chat_history' => $messages]);

                return response()->json(['response' => $aiMessage]);
            } else {
                // エラーレスポンスを処理
                return response()->json([
                    'response' => 'AIとの通信でエラーが発生しました。',
                    'error' => $response->json(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // エラーが発生した場合の処理
            return response()->json([
                'response' => 'サーバーエラーが発生しました。',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ディベートをクリアする
    public function resetChatHistory()
    {
        session()->forget('chat_history');
        return response()->json(['message' => 'ディベートの内容をリセットしました。']);
    }
}
