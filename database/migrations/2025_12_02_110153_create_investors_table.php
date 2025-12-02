<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->index('idx_inv_user');
            $table->string('name');
            $table->string('nif')->unique();
            $table->string('phone');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investors');
    }
};