<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('opponents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 有名人の名前
            $table->string('image')->nullable(); // アイコン画像のURL
            $table->text('system_message'); // 人格形成用のシステムメッセージ
            $table->timestamps();
            $table->softDeletes(); // 🔹 論理削除を追加
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opponents');
    }
};

