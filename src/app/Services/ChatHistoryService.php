<?php

namespace App\Services;

use App\Models\ChatHistory;
use Illuminate\Support\Facades\Log;

class ChatHistoryService
{
    /**
     * 🔹 セッションIDに基づくチャット履歴を取得
     */
    public function getChatHistory(string $sessionId)
    {
        return ChatHistory::getHistoryBySession($sessionId);
    }

    /**
     * 🔹 ユーザーのメッセージを履歴に追加
     */
    public function addUserMessage(string $sessionId, string $userMessage)
    {
        $chatHistory = ChatHistory::firstOrCreate(
            ['session_id' => $sessionId],
            ['messages' => []]
        );

        $messages = $chatHistory->messages;
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $chatHistory->update(['messages' => $messages]);

        return $messages;
    }

    /**
     * 🔹 AIのレスポンスを履歴に追加
     */
    public function addAiMessage(string $sessionId, string $aiMessage)
    {
        $chatHistory = ChatHistory::where('session_id', $sessionId)->first();
        if ($chatHistory) {
            $messages = $chatHistory->messages;
            $messages[] = ['role' => 'assistant', 'content' => $aiMessage];

            $chatHistory->update(['messages' => $messages]);
        }
    }

    /**
     * 🔹 チャット履歴をリセット
     */
    public function resetChatHistory(string $sessionId)
    {
        ChatHistory::where('session_id', $sessionId)->delete();
    }
}
