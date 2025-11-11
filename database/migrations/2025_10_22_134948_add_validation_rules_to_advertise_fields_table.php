<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('advertise_fields', function (Blueprint $table) {
            
            if (!Schema::hasColumn('advertise_fields', 'validation_message')) {
                $table->string('validation_message')->nullable()->after('validation_rules');
            }
        });
    }

    public function down()
    {
        Schema::table('advertise_fields', function (Blueprint $table) {
            $table->dropColumn(['validation_rules', 'validation_message']);
        });
    }
};