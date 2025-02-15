<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'image', 'system_message'];

    public function chatHistories()
    {
        return $this->hasMany(ChatHistory::class, 'opponent_id');
    }
}
