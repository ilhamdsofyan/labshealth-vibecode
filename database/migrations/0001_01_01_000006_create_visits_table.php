<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date');
            $table->time('visit_time');
            $table->string('patient_name');
            $table->enum('gender', ['L', 'P']);
            $table->enum('patient_category', ['SMA', 'GURU', 'KARYAWAN', 'UMUM']);
            $table->string('class_or_department')->nullable();
            $table->text('complaint');
            $table->text('therapy')->nullable();
            $table->string('officer_name');
            $table->text('notes')->nullable();
            $table->enum('visit_type', ['kunjungan', 'acc_pulang'])->default('kunjungan');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('visit_date');
            $table->index('patient_category');
            $table->index('visit_type');
            $table->index('class_or_department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
