<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_token', 'opponent_id', 'messages'];

    protected $casts = [
        'messages' => 'array',
    ];

    public function opponent()
    {
        return $this->belongsTo(Opponent::class, 'opponent_id');
    }

    /**
     * 🔹 `user_token` + `opponent_id` で履歴を取得 or 新規作成
     */
    public static function getChatHistory(string $userToken, int $opponentId): self
    {
        \Log::info('🔍 getChatHistory: userToken の値を確認', [
            'userToken' => $userToken,
            'length' => strlen($userToken),
            'opponentId' => $opponentId
        ]);
    
        return self::firstOrCreate(
            ['user_token' => $userToken, 'opponent_id' => $opponentId],
            ['messages' => []]
        );
    }
    
    /**
     * 🔹 新しいメッセージを追加
     */
    public function addMessage(string $role, string $content): void
    {
        $messages = $this->messages ?? [];
        $messages[] = ['role' => $role, 'content' => $content];

        $this->update(['messages' => $messages]);
    }

    /**
     * 🔹 チャット履歴をリセット（論理削除）
     */
    public static function deleteChatHistory(string $userToken, int $opponentId): void
    {
        self::where('user_token', $userToken)
            ->where('opponent_id', $opponentId)
            ->delete();
    }
}
