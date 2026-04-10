<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_agendas', function (Blueprint $table) {
            $table->dropIndex('agendas_creator_date_idx');
        });

        Schema::table('disease_visit', function (Blueprint $table) {
            $table->dropIndex('disease_visit_disease_id_foreign');
        });

        Schema::table('medication_visit', function (Blueprint $table) {
            $table->dropIndex('medication_visit_medication_id_foreign');
        });

        Schema::table('menu_role', function (Blueprint $table) {
            $table->dropIndex('menu_role_role_id_foreign');
        });

        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropIndex('permission_role_role_id_foreign');
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->dropIndex('role_user_role_id_foreign');
        });

        Schema::table('student_class_histories', function (Blueprint $table) {
            $table->dropIndex('student_class_histories_student_id_foreign');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_patient_category_index');
            $table->dropIndex('visits_visit_date_visit_time_idx');
            $table->dropIndex('visits_student_id_index');
            $table->dropIndex('visits_visit_date_index');
            $table->dropIndex('visits_employee_id_index');
            $table->dropIndex('visits_is_acc_pulang_index');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_agendas', function (Blueprint $table) {
            $table->index(['created_by', 'agenda_date'], 'agendas_creator_date_idx');
        });

        Schema::table('disease_visit', function (Blueprint $table) {
            $table->index('disease_id', 'disease_visit_disease_id_foreign');
        });

        Schema::table('medication_visit', function (Blueprint $table) {
            $table->index('medication_id', 'medication_visit_medication_id_foreign');
        });

        Schema::table('menu_role', function (Blueprint $table) {
            $table->index('role_id', 'menu_role_role_id_foreign');
        });

        Schema::table('permission_role', function (Blueprint $table) {
            $table->index('role_id', 'permission_role_role_id_foreign');
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->index('role_id', 'role_user_role_id_foreign');
        });

        Schema::table('student_class_histories', function (Blueprint $table) {
            $table->index('student_id', 'student_class_histories_student_id_foreign');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->index('patient_category', 'visits_patient_category_index');
            $table->index(['visit_date', 'visit_time'], 'visits_visit_date_visit_time_idx');
            $table->index('student_id', 'visits_student_id_index');
            $table->index('visit_date', 'visits_visit_date_index');
            $table->index('employee_id', 'visits_employee_id_index');
            $table->index('is_acc_pulang', 'visits_is_acc_pulang_index');
        });
    }
};
