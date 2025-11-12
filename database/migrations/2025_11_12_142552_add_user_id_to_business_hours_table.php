<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('business_hours', function (Blueprint $table) {
            // 🔥 ADICIONA O CAMPO user_id
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            // 🔥 ADICIONA ÍNDICE PARA MELHOR PERFORMANCE
            $table->index('user_id');
            $table->index(['user_id', 'advertise_id']);
        });
    }

    public function down(): void
    {
        Schema::table('business_hours', function (Blueprint $table) {
            // 🔥 REMOVE NO ROLLBACK
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_id', 'advertise_id']);
        });
    }
};