<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('class_or_department', 120)->nullable()->change();
            $table->string('officer_name', 120)->change();

            $table->index(['visit_date', 'visit_time'], 'visits_date_time_idx');
            $table->index(['student_id', 'visit_date'], 'visits_student_date_idx');
            $table->index(['employee_id', 'visit_date'], 'visits_employee_date_idx');
            $table->index(['patient_category', 'visit_date'], 'visits_category_date_idx');
            $table->index(['is_acc_pulang', 'visit_date'], 'visits_acc_date_idx');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('nis', 30)->change();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('nip', 30)->change();
            $table->string('department', 120)->nullable()->change();

            $table->index(['role_type', 'department'], 'employees_role_department_idx');
        });

        Schema::table('student_class_histories', function (Blueprint $table) {
            $table->index(['student_id', 'is_active'], 'sch_student_active_idx');
        });

        Schema::table('clinic_agendas', function (Blueprint $table) {
            $table->index(['agenda_date', 'is_public'], 'agendas_date_public_idx');
            $table->index(['created_by', 'agenda_date'], 'agendas_creator_date_idx');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->index(['is_active', 'parent_id', 'order'], 'menus_active_parent_order_idx');
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->index(['role_id', 'user_id'], 'role_user_role_user_idx');
        });

        Schema::table('permission_role', function (Blueprint $table) {
            $table->index(['role_id', 'permission_id'], 'permission_role_role_permission_idx');
        });

        Schema::table('menu_role', function (Blueprint $table) {
            $table->index(['role_id', 'menu_id'], 'menu_role_role_menu_idx');
        });

        Schema::table('disease_visit', function (Blueprint $table) {
            $table->index(['disease_id', 'visit_id'], 'disease_visit_disease_visit_idx');
        });

        Schema::table('medication_visit', function (Blueprint $table) {
            $table->index(['medication_id', 'visit_id'], 'medication_visit_medication_visit_idx');
        });
    }

    public function down(): void
    {
        Schema::table('medication_visit', function (Blueprint $table) {
            $table->dropIndex('medication_visit_medication_visit_idx');
        });

        Schema::table('disease_visit', function (Blueprint $table) {
            $table->dropIndex('disease_visit_disease_visit_idx');
        });

        Schema::table('menu_role', function (Blueprint $table) {
            $table->dropIndex('menu_role_role_menu_idx');
        });

        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropIndex('permission_role_role_permission_idx');
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->dropIndex('role_user_role_user_idx');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex('menus_active_parent_order_idx');
        });

        Schema::table('clinic_agendas', function (Blueprint $table) {
            $table->dropIndex('agendas_date_public_idx');
            $table->dropIndex('agendas_creator_date_idx');
        });

        Schema::table('student_class_histories', function (Blueprint $table) {
            $table->dropIndex('sch_student_active_idx');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('employees_role_department_idx');
            $table->string('nip')->change();
            $table->string('department')->nullable()->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('nis')->change();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_date_time_idx');
            $table->dropIndex('visits_student_date_idx');
            $table->dropIndex('visits_employee_date_idx');
            $table->dropIndex('visits_category_date_idx');
            $table->dropIndex('visits_acc_date_idx');

            $table->string('class_or_department')->nullable()->change();
            $table->string('officer_name')->change();
        });
    }
};
