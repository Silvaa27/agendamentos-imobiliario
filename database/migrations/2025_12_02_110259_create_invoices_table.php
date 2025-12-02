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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_opportunity_id')
                ->constrained('investment_opportunities')
                ->onDelete('cascade')
                ->index('idx_inv_opportunity');

            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->string('invoice_number')->nullable();
            $table->date('date');
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
