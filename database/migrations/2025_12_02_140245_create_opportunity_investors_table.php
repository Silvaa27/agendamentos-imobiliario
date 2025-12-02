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
        Schema::create('opportunity_investors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->foreignId('investor_id')->constrained()->onDelete('cascade');
            $table->decimal('investment_amount', 15, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('has_access')->default(false);
            $table->timestamp('access_granted_at')->nullable();
            $table->timestamps();

            $table->unique(['opportunity_id', 'investor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunity_investors');
    }
};
