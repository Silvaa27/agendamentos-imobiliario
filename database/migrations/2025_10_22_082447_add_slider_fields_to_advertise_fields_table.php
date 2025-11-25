<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertise_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('advertise_fields', 'min_value')) {
                $table->integer('min_value')->nullable()->after('answer');
            }

            if (!Schema::hasColumn('advertise_fields', 'max_value')) {
                $table->integer('max_value')->nullable()->after('min_value');
            }

            if (!Schema::hasColumn('advertise_fields', 'step')) {
                $table->integer('step')->nullable()->after('max_value');
            }

            if (!Schema::hasColumn('advertise_fields', 'options')) {
                $table->json('options')->nullable()->after('is_required');
            }
        });
    }

    public function down(): void
    {
        Schema::table('advertise_fields', function (Blueprint $table) {
            $table->dropColumn(['min_value', 'max_value', 'step', 'options']);
        });
    }
};