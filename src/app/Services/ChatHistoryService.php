<?php

namespace App\Services;

use App\Models\ChatHistory;
use App\Models\Opponent;
use App\Services\AiService;

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
    public function handleChatMessage(string $userToken, int $opponentId, string $userMessage): string
    {
        // 🔹 履歴を取得 or 作成
        $chatHistory = ChatHistory::getChatHistory($userToken, $opponentId);

        // 🔹 ユーザーのメッセージを追加
        $chatHistory->addMessage('user', $userMessage);

        // 🔹 AIのレスポンスを取得
        $opponent = Opponent::findOrDefault($opponentId);
        $aiMessage = $this->aiService->getAiResponse($chatHistory->messages, $opponent->id);

        // 🔹 AIのメッセージを追加
        $chatHistory->addMessage('assistant', $aiMessage);

        return $aiMessage;
    }
}
