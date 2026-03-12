<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disease_visit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('disease_id')->constrained('diseases')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['visit_id', 'disease_id']);
        });

        Schema::create('medication_visit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['visit_id', 'medication_id']);
        });

        $now = now();

        DB::table('visits')
            ->select(['id', 'disease_id'])
            ->whereNotNull('disease_id')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($now) {
                $payload = [];
                foreach ($rows as $row) {
                    $payload[] = [
                        'visit_id' => $row->id,
                        'disease_id' => $row->disease_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($payload)) {
                    DB::table('disease_visit')->upsert(
                        $payload,
                        ['visit_id', 'disease_id'],
                        ['updated_at']
                    );
                }
            });

        DB::table('visits')
            ->select(['id', 'medication_id'])
            ->whereNotNull('medication_id')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($now) {
                $payload = [];
                foreach ($rows as $row) {
                    $payload[] = [
                        'visit_id' => $row->id,
                        'medication_id' => $row->medication_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($payload)) {
                    DB::table('medication_visit')->upsert(
                        $payload,
                        ['visit_id', 'medication_id'],
                        ['updated_at']
                    );
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_visit');
        Schema::dropIfExists('disease_visit');
    }
};
