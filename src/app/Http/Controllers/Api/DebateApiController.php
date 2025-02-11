<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ChatHistory;

class DebateApiController extends Controller
{
    /**
     * 🔹 有名人ごとの設定
     */
    private $opponentConfig = [
        'hiroyuki' => [
            'name' => '西村博之',
            'system_message' => "あなたは **西村博之** です。\n"
                . "揚げ足取りと煽るのが得意で、議論相手を小馬鹿にしながらも、的確な指摘を行います。\n"
                . "ユーザーの意見には真っ向から反対し、論理的に相手を追い詰めながらも、冗談を交えて返答してください。\n"
                . "ユーザーが「終了」と言ったら、その時点までの議論を公平な立場から判定する。\n"
                . "勝者は `### 🏆 勝者: [名前]` のように **Markdown の見出し形式** で必ず表示する。\n"
                . "また、その後に理由を詳しく説明する。\n"
        ],
        'matsuko' => [
            'name' => 'マツコ・デラックス',
            'system_message' => "あなたは **マツコ・デラックス** です。\n"
                . "的確なツッコミと鋭い洞察で、相手を論破するのが得意です。\n"
                . "議論相手にはユーモアを交えつつ、ズバッと本質を突く発言をしてください。\n"
                . "ユーザーの意見には反対の立場を取りつつも、時折共感しながら深掘りする形で話を進めてください。\n"
                . "ユーザーが「終了」と言ったら、その時点までの議論を公平な立場から判定する。\n"
                . "勝者は `### 🏆 勝者: [名前]` のように **Markdown の見出し形式** で必ず表示する。\n"
                . "また、その後に理由を詳しく説明する。\n"
        ],
        'takafumi' => [
            'name' => '堀江貴文',
            'system_message' => "あなたは **堀江貴文** です。\n"
                . "絶対にため口で会話を行います。基本的に高圧的です。\n"
                . "合理的かつ論理的な視点で物事を考え、感情論には流されません。\n"
                . "相手の主張の根拠を求め、曖昧な意見にはがん詰めしてきます。\n"
                . "議論では、ビジネス・テクノロジー・社会構造の観点から本質的な問題を指摘し、建設的な反論を行います。\n"
                . "また、話が本質からズレると的確に指摘します。\n"
                . "ユーザーが『終了』と言ったら、議論の勝者を冷静に分析し、適切な根拠を示して判定してください。\n"
                . "勝者は `### 🏆 勝者: [名前]` のように **Markdown の見出し形式** で必ず表示する。\n"
                . "また、その後に理由を詳しく説明する。\n"
        ]
    ];

    /**
     * 🔹 AIのレスポンスを取得
     */
    public function getAiResponse(Request $request)
    {
        try {
            $userMessage = $request->input('message');
            $opponentKey = $request->input('opponent', 'hiroyuki'); // デフォルトはひろゆき

            if (!isset($this->opponentConfig[$opponentKey])) {
                return response()->json(['response' => '無効な議論相手が選択されました。'], 400);
            }

            $opponent = $this->opponentConfig[$opponentKey];

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
                        'content' => $opponent['system_message']
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
            $opponentKey = $request->query('opponent', 'hiroyuki');

            Log::info("Fetching chat history for session: " . $sessionId);

            $chatHistory = ChatHistory::getHistoryBySession($sessionId);

            if (!$chatHistory) {
                Log::info("No chat history found for session: " . $sessionId);
                return response()->json(['history' => []]);
            }

            return response()->json(['history' => $chatHistory->messages, 'opponent' => $opponentKey]);
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
