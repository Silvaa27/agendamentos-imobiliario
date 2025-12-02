<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('construction_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('title');
            $table->text('report')->nullable(); // descrição/relatório
            $table->decimal('progress_percentage', 5, 2)->nullable(); // progresso %
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // responsável
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('construction_updates');
    }
};
