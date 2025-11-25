<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('advertise_field_answers', function (Blueprint $table) {
            $table->dropForeign(['advertise_field_id']);
        });

        // Recriar com cascade
        Schema::table('advertise_field_answers', function (Blueprint $table) {
            $table->foreign('advertise_field_id')
                  ->references('id')
                  ->on('advertise_fields')
                  ->onDelete('cascade');
        });
        Schema::table('advertise_field_answers', function (Blueprint $table) {
            $table->dropForeign(['advertise_answer_id']);
        });

        Schema::table('advertise_field_answers', function (Blueprint $table) {
            $table->foreign('advertise_answer_id')
                  ->references('id')
                  ->on('advertise_answers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('advertise_field_answers', function (Blueprint $table) {
            $table->dropForeign(['advertise_field_id']);
            $table->foreign('advertise_field_id')
                  ->references('id')
                  ->on('advertise_fields');
        });

        Schema::table('advertise_field_answers', function (Blueprint $table) {
            $table->dropForeign(['advertise_answer_id']);
            $table->foreign('advertise_answer_id')
                  ->references('id')
                  ->on('advertise_answers');
        });
    }
};