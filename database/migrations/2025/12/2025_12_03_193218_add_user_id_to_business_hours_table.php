<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // In the created migration file:
    public function up()
    {
        Schema::table('business_hours', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('business_hours', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
