<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('opportunity_investor', function (Blueprint $table) {
            // Dropar a constraint existente
            $table->dropForeign('opportunity_investors_investor_id_foreign');

            // Recriar apontando para users
            $table->foreign('investor_id', 'opportunity_investors_investor_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('opportunity_investor', function (Blueprint $table) {
            $table->dropForeign('opportunity_investors_investor_id_foreign');
            $table->foreign('investor_id', 'opportunity_investors_investor_id_foreign')
                ->references('id')
                ->on('investors')
                ->onDelete('cascade');
        });
    }
};
