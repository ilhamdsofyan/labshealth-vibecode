<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_agendas', function (Blueprint $table) {
            if (!Schema::hasColumn('clinic_agendas', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinic_agendas', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_agendas', 'is_public')) {
                $table->dropColumn('is_public');
            }
        });
    }
};
