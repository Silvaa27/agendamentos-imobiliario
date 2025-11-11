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
        Schema::create('advertise_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertise_id')->constrained('advertises')->onDelete('cascade');
            $table->string('answer');
            $table->string('validation_rules');
            $table->enum('field_type', ['TextInput', 'Select', 'Checkbox', 'Toggle', 'CheckboxList', 'Radio', 'DatePicker', 'TimePicker', 'Slider', 'Textarea']);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertise_fields');
    }
};
