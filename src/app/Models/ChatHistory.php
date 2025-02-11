<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'messages'];

    protected $casts = [
        'messages' => 'json',
    ];

    /**
     * 🔹 セッションIDに基づいて履歴を取得
     */
    public static function getHistoryBySession($sessionId)
    {
        return self::where('session_id', $sessionId)->first();
    }
}
