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
        Schema::create('io_investor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_opportunity_id')
                ->constrained('investment_opportunities')
                ->onDelete('cascade')
                ->index('idx_ioi_opportunity');

            $table->foreignId('investor_id')
                ->constrained('investors')
                ->onDelete('cascade')
                ->index('idx_ioi_investor');

            $table->boolean('has_access')->default(true);
            $table->timestamps();

            // Chave única para evitar duplicações
            $table->unique(['investment_opportunity_id', 'investor_id'], 'uidx_io_investor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('io_investor');
    }
};
