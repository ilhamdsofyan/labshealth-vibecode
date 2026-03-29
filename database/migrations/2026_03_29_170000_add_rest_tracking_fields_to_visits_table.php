<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'rest_started_at')) {
                $table->timestamp('rest_started_at')->nullable()->after('is_rest');
            }

            if (! Schema::hasColumn('visits', 'rest_ended_at')) {
                $table->timestamp('rest_ended_at')->nullable()->after('rest_started_at');
            }

            if (! Schema::hasColumn('visits', 'rest_status')) {
                $table->string('rest_status', 20)->nullable()->after('rest_ended_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            foreach (['rest_status', 'rest_ended_at', 'rest_started_at'] as $column) {
                if (Schema::hasColumn('visits', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
