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
        Schema::create('investment_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('gallery')->nullable();
            $table->decimal('worst_case_price', 15, 2);
            $table->decimal('market_price', 15, 2);
            $table->decimal('budgeted_renovation_cost', 15, 2);
            $table->decimal('actual_renovation_cost', 15, 2)->nullable();
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('other_costs', 15, 2)->default(0);
            $table->decimal('tax_costs', 15, 2)->default(0);
            $table->string('opportunity_url')->nullable();
            $table->enum('status', [
                'em_avaliacao',
                'em_negociacao',
                'em_obras',
                'em_venda'
            ])->default('em_avaliacao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_opportunities');
    }
};
