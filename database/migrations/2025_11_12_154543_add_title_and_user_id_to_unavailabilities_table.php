<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('unavailabilities', function (Blueprint $table) {
            $table->string('title')->after('id');
            $table->foreignId('user_id')->nullable()->after('title')->constrained()->onDelete('cascade');

            // Se quiseres índices para melhor performance
            $table->index('user_id');
            $table->index('start');
            $table->index('end');
        });
    }

    public function down()
    {
        Schema::table('unavailabilities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['title', 'user_id']);
        });
    }
};
