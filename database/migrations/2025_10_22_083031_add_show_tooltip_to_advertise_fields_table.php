<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertise_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('advertise_fields', 'show_tooltip')) {
                $table->boolean('show_tooltip')->default(false)->after('step');
            }
        });
    }

    public function down(): void
    {
        Schema::table('advertise_fields', function (Blueprint $table) {
            $table->dropColumn('show_tooltip');
        });
    }
};
