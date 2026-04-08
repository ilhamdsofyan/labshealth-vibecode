<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'offline_client_uuid')) {
                $table->uuid('offline_client_uuid')->nullable()->unique()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'offline_client_uuid')) {
                $table->dropUnique(['offline_client_uuid']);
                $table->dropColumn('offline_client_uuid');
            }
        });
    }
};
