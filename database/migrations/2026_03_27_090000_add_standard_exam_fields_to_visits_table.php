<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->decimal('height_cm', 5, 2)->nullable()->after('class_or_department');
            $table->decimal('weight_kg', 5, 2)->nullable()->after('height_cm');
            $table->string('blood_pressure', 20)->nullable()->after('weight_kg');
            $table->unsignedSmallInteger('heart_rate')->nullable()->after('blood_pressure');
            $table->unsignedSmallInteger('respiratory_rate')->nullable()->after('heart_rate');
            $table->decimal('temperature_c', 4, 2)->nullable()->after('respiratory_rate');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn([
                'height_cm',
                'weight_kg',
                'blood_pressure',
                'heart_rate',
                'respiratory_rate',
                'temperature_c',
            ]);
        });
    }
};
