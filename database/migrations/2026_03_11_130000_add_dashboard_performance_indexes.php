<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->index(['visit_date', 'visit_time'], 'visits_visit_date_visit_time_idx');
            $table->index(['visit_date', 'patient_category'], 'visits_visit_date_patient_category_idx');
            $table->index(['is_rest', 'is_acc_pulang', 'bed_id'], 'visits_rest_occupancy_idx');
        });

        Schema::table('clinic_agendas', function (Blueprint $table) {
            $table->index(['is_public', 'agenda_date', 'agenda_time'], 'agendas_public_date_time_idx');
            $table->index(['created_by', 'agenda_date', 'agenda_time'], 'agendas_creator_date_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_visit_date_visit_time_idx');
            $table->dropIndex('visits_visit_date_patient_category_idx');
            $table->dropIndex('visits_rest_occupancy_idx');
        });

        Schema::table('clinic_agendas', function (Blueprint $table) {
            $table->dropIndex('agendas_public_date_time_idx');
            $table->dropIndex('agendas_creator_date_time_idx');
        });
    }
};
