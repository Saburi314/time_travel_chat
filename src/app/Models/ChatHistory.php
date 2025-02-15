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
     * 🔹 `user_token` + `opponent_id` で履歴を取得
     */
    public static function getChatHistory(string $userToken, int $opponentId)
    {
        return self::where('user_token', $userToken)
            ->where('opponent_id', $opponentId)
            ->first();
    }

    /**
     * 🔹 新しいメッセージを追加
     */
    public function addMessage(string $role, string $content)
    {
        $messages = $this->messages ?? [];
        $messages[] = ['role' => $role, 'content' => $content];
        $this->messages = $messages;
        $this->save();
    }

    /**
     * 🔹 ユーザーの履歴をリセット（論理削除）
     */
    public static function deleteChatHistory(string $userToken, int $opponentId)
    {
        return self::where('user_token', $userToken)
            ->where('opponent_id', $opponentId)
            ->delete();
    }
}
