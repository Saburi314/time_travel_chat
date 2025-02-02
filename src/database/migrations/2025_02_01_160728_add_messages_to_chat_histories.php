<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->json('messages')->nullable()->after('session_id'); // JSON型で追加
        });
    }

    public function down()
    {
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->dropColumn('messages');
        });
    }
};
