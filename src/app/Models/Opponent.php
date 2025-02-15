<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'image', 'system_message'];

    /**
     * 🔹 ID から Opponent を取得（見つからなければデフォルト ID=1）
     */
    public static function getOpponent(int $opponentId): ?self
    {
        return self::where('id', $opponentId)->first() ?? self::findOrFail(1);
    }
}
