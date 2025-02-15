<?php

namespace App\Services;

use App\Models\ChatHistory;
use App\Models\Opponent;
use App\Services\AiService;
use Illuminate\Support\Facades\Log;
use Exception;

class ChatHistoryService
{
    private AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * 🔹 ユーザーの発言を追加し、AI のレスポンスを取得
     */
    public function handleChatMessage(string $userToken, int $opponentId, ?string $userMessage = ''): string
    {
        Log::info('handleChatMessage: メッセージ処理開始', [
            'userToken' => $userToken,
            'opponentId' => $opponentId,
            'userMessage' => $userMessage ?? '(null)'
        ]);

        try {
            // 🔹 チャット履歴を取得
            Log::info('handleChatMessage: チャット履歴の取得を試みます');
            $chatHistory = ChatHistory::getChatHistory($userToken, $opponentId);

            if (!$chatHistory) {
                Log::error('handleChatMessage: ChatHistoryが取得できません', [
                    'userToken' => $userToken,
                    'opponentId' => $opponentId
                ]);
                return "エラー: チャット履歴を取得できませんでした。";
            }

            Log::info('handleChatMessage: チャット履歴取得成功', ['messages' => $chatHistory->messages]);

            // 🔹 ユーザーメッセージを追加
            Log::info('handleChatMessage: ユーザーメッセージを追加');
            $chatHistory->addMessage('user', $userMessage);
            Log::info('handleChatMessage: ユーザーメッセージ追加完了');

            // 🔹 AIのレスポンス取得
            Log::info('handleChatMessage: Opponentの取得を試みます');
            $opponent = Opponent::getOpponent($opponentId);

            if (!$opponent) {
                Log::error('handleChatMessage: Opponent が見つかりません', ['opponentId' => $opponentId]);
                return "エラー: Opponentが見つかりませんでした。";
            }

            Log::info('handleChatMessage: Opponent取得成功', ['opponent' => $opponent]);

            Log::info('handleChatMessage: AIレスポンスの取得を試みます');
            $aiMessage = $this->aiService->getAiResponse($chatHistory->messages, $opponent->id);
            Log::info('handleChatMessage: AIレスポンス取得成功', ['aiMessage' => $aiMessage]);

            // 🔹 AIメッセージを追加
            Log::info('handleChatMessage: AIメッセージをチャット履歴に追加');
            $chatHistory->addMessage('assistant', $aiMessage);
            Log::info('handleChatMessage: AIメッセージ追加完了');

            return $aiMessage;
        } catch (Exception $e) {
            Log::error('handleChatMessage: 処理中にエラー発生', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return "エラー: メッセージ処理中に問題が発生しました。";
        }
    }
}
