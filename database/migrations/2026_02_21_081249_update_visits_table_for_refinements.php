<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('disease_id')->nullable()->after('id')->constrained('diseases');
            $table->foreignId('student_id')->nullable()->after('disease_id')->constrained('students');
            $table->foreignId('employee_id')->nullable()->after('student_id')->constrained('employees');
            
            $table->boolean('is_acc_pulang')->default(false)->after('visit_type');
            $table->text('acc_pulang_reason')->nullable()->after('is_acc_pulang');
            $table->string('class_at_visit')->nullable()->after('acc_pulang_reason');
            
            $table->softDeletes();
            
            $table->index('disease_id');
            $table->index('student_id');
            $table->index('employee_id');
            $table->index('is_acc_pulang');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['disease_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['employee_id']);
            
            $table->dropColumn(['disease_id', 'student_id', 'employee_id', 'is_acc_pulang', 'acc_pulang_reason', 'class_at_visit', 'deleted_at']);
        });
    }
};
