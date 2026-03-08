<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_agendas', function (Blueprint $table) {
            $table->id();
            $table->date('agenda_date');
            $table->time('agenda_time')->nullable();
            $table->string('title');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['agenda_date', 'agenda_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_agendas');
    }
};
