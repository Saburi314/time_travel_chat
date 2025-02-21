<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Opponent;

class AiService
{
    /**
     * 🔹 OpenAI API を呼び出し、レスポンスを取得
     */
    public function getAiResponse(array $messages, int $opponentId): ?string
    {
        try {
            $apiKey = env('CHAT_GPT_API_KEY');
            $url = 'https://api.openai.com/v1/chat/completions';

            $opponent = Opponent::getOpponent($opponentId);
            $systemMessage = [['role' => 'system', 'content' => $opponent->system_message]];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'model' => 'gpt-4o',
                'messages' => array_merge($systemMessage, $messages),
                'temperature' => 1.0,
                'max_tokens' => 600,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error("AI API エラー", ['error' => $response->json()]);
            return null;
        } catch (\Exception $e) {
            Log::error("AI API 通信エラー: " . $e->getMessage());
            return null;
        }
    }
}
