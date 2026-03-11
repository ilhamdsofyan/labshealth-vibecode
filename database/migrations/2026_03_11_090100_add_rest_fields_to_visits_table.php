<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (!Schema::hasColumn('visits', 'is_rest')) {
                $table->boolean('is_rest')->default(false)->after('is_acc_pulang');
            }

            if (!Schema::hasColumn('visits', 'bed_id')) {
                $table->foreignId('bed_id')->nullable()->after('is_rest')->constrained('beds')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'bed_id')) {
                $table->dropConstrainedForeignId('bed_id');
            }

            if (Schema::hasColumn('visits', 'is_rest')) {
                $table->dropColumn('is_rest');
            }
        });
    }
};
