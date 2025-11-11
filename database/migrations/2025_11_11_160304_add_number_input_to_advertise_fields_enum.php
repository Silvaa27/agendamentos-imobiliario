<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE advertise_fields MODIFY COLUMN field_type ENUM(
            'TextInput',
            'NumberInput',
            'Select', 
            'Checkbox',
            'Toggle',
            'CheckboxList',
            'Radio',
            'DatePicker',
            'TimePicker',
            'Slider',
            'Textarea'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE advertise_fields MODIFY COLUMN field_type ENUM(
            'TextInput',
            'Select',
            'Checkbox',
            'Toggle', 
            'CheckboxList',
            'Radio',
            'DatePicker',
            'TimePicker',
            'Slider',
            'Textarea'
        ) NOT NULL");
    }
};