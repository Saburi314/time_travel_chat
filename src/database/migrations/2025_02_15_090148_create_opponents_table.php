<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('opponents', function (Blueprint $table) {
            $table->id(); // 主キー
            $table->string('name')->unique(); // 議論相手の名前
            $table->string('image')->nullable(); // アイコン画像URL
            $table->text('system_message')->nullable(); // AIのシステムメッセージ
            $table->timestamps(); // 作成日時・更新日時
            $table->softDeletes(); // 論理削除カラム（deleted_at）
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opponents');
    }
};
