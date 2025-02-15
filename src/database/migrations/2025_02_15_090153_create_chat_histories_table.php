<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_histories', function (Blueprint $table) {
            $table->id(); // 主キー
            $table->string('user_token')->index(); // ユーザー識別用トークン（Cookie に保存）
            $table->foreignId('opponent_id')->constrained()->onDelete('cascade'); // 対象の相手
            $table->json('messages')->nullable(); // チャット履歴
            $table->timestamps(); // 作成日時・更新日時
            $table->softDeletes(); // 論理削除カラム（deleted_at）
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_histories');
    }
};
