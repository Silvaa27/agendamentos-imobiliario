<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('property_info')->nullable(); // informação do imóvel
            $table->decimal('price_worst_case', 15, 2)->nullable(); // preço pior cenário
            $table->decimal('price_market', 15, 2)->nullable(); // preço cenário mercado
            $table->decimal('budgeted_work_value', 15, 2)->nullable(); // valor orçamentado
            $table->decimal('actual_work_value', 15, 2)->nullable(); // valor com desvio
            $table->decimal('purchase_price', 15, 2)->nullable(); // preço compra
            $table->decimal('other_costs', 15, 2)->nullable(); // outros custos
            $table->decimal('tax_costs', 15, 2)->nullable(); // custos com impostos
            $table->string('opportunity_link')->nullable(); // link de oportunidade
            $table->enum('status', ['em_avaliacao', 'em_negociacao', 'em_obras', 'em_venda', 'concluido'])->default('em_avaliacao');
            $table->boolean('has_investment_program')->default(false); // tem programa de investidores associado
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // responsável
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};