<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->nullable();
            $table->enum('rhesus', ['+', '-'])->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_diseases')->nullable();
            $table->text('past_surgeries')->nullable();
            $table->text('regular_medications')->nullable();
            $table->date('last_checkup_date')->nullable();
            $table->text('medical_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_medical_records');
    }
};
