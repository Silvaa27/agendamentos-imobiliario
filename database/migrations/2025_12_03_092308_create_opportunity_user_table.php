<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_opportunity_user_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('opportunity_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->unique(['opportunity_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_user');
    }
};
