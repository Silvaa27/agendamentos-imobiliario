<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advertise_field_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertise_answer_id')->constrained('advertise_answers')->onDelete('cascade');
            $table->foreignId('advertise_field_id')->constrained('advertise_fields');
            $table->json('answer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertise_field_answers');
    }
};
