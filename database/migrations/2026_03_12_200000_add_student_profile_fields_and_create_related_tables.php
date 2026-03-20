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
        Schema::table('students', function (Blueprint $table) {
            $table->string('nickname', 80)->nullable()->after('name');
            $table->string('class_name', 30)->nullable()->after('gender');
            $table->string('nisn', 10)->nullable()->unique()->after('class_name');
            $table->string('nik_kitas', 24)->nullable()->after('nisn');
            $table->string('family_card_number', 24)->nullable()->after('nik_kitas');
            $table->string('birth_place', 80)->nullable()->after('family_card_number');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('birth_certificate_number', 50)->nullable()->after('birth_date');
            $table->string('religion', 20)->nullable()->after('birth_certificate_number');
            $table->string('citizenship', 30)->nullable()->after('religion');
            $table->string('daily_language', 40)->nullable()->after('citizenship');
            $table->string('whatsapp_number', 20)->nullable()->after('daily_language');
            $table->string('email', 120)->nullable()->after('whatsapp_number');
            $table->text('address_text')->nullable()->after('email');
            $table->text('notes')->nullable()->after('address_text');
        });

        Schema::create('student_health', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('head_circumference_cm', 5, 2)->nullable();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->nullable();
            $table->enum('rhesus', ['+', '-'])->nullable();
            $table->string('eye_condition', 60)->nullable();
            $table->boolean('has_eye_disorder')->default(false);
            $table->string('assistive_device', 80)->nullable();
            $table->string('ear_condition', 60)->nullable();
            $table->boolean('uses_hearing_aid')->default(false);
            $table->string('face_shape', 40)->nullable();
            $table->string('hair_type', 40)->nullable();
            $table->string('skin_tone', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('student_medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->text('past_diseases')->nullable();
            $table->boolean('ever_hospitalized')->default(false);
            $table->boolean('has_recurring_disease')->default(false);
            $table->text('surgery_history')->nullable();
            $table->text('relapse_treatment')->nullable();
            $table->text('drug_food_allergies')->nullable();
            $table->timestamps();
        });

        Schema::create('student_previous_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->string('smp_school_name', 150)->nullable();
            $table->string('smp_npsn', 8)->nullable();
            $table->unsignedSmallInteger('smp_study_duration_months')->nullable();
            $table->boolean('ever_repeated_grade')->default(false);
            $table->text('achievements')->nullable();
            $table->boolean('receives_scholarship')->default(false);
            $table->text('extracurricular_smp')->nullable();
            $table->timestamps();
        });

        Schema::create('student_learning_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->string('sports_hobby', 80)->nullable();
            $table->string('arts_hobby', 80)->nullable();
            $table->string('other_hobby', 80)->nullable();
            $table->string('talent_field', 80)->nullable();
            $table->boolean('has_leisure_time')->default(false);
            $table->unsignedSmallInteger('reading_start_age_months')->nullable();
            $table->unsignedSmallInteger('writing_start_age_months')->nullable();
            $table->unsignedSmallInteger('counting_start_age_months')->nullable();
            $table->unsignedSmallInteger('speaking_start_age_months')->nullable();
            $table->unsignedSmallInteger('start_kb_tk_age_months')->nullable();
            $table->unsignedSmallInteger('start_sd_age_months')->nullable();
            $table->unsignedSmallInteger('start_smp_age_months')->nullable();
            $table->boolean('likes_school')->default(false);
            $table->string('likes_play_with', 80)->nullable();
            $table->string('likes_game_type', 80)->nullable();
            $table->string('preferred_activity', 80)->nullable();
            $table->string('concentration_level', 40)->nullable();
            $table->string('task_completion_style', 40)->nullable();
            $table->string('imagination_role', 100)->nullable();
            $table->boolean('has_home_study_group')->default(false);
            $table->boolean('study_group_beneficial')->default(false);
            $table->boolean('attends_tutoring')->default(false);
            $table->string('tutoring_institution', 120)->nullable();
            $table->decimal('self_study_hours_per_day', 4, 2)->nullable();
            $table->boolean('has_home_study_schedule')->default(false);
            $table->string('common_study_time', 40)->nullable();
            $table->boolean('asks_curiosity_questions')->default(false);
            $table->text('curiosity_topics')->nullable();
            $table->timestamps();
        });

        Schema::create('student_assets_home', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->decimal('home_to_school_distance_km', 5, 2)->nullable();
            $table->unsignedSmallInteger('home_to_school_travel_minutes')->nullable();
            $table->string('transport_mode', 40)->nullable();
            $table->string('household_vehicle', 120)->nullable();
            $table->string('living_environment', 80)->nullable();
            $table->string('home_lighting_condition', 60)->nullable();
            $table->string('bedroom_condition', 60)->nullable();
            $table->string('study_room_condition', 60)->nullable();
            $table->string('learning_tools', 160)->nullable();
            $table->boolean('has_musical_instruments')->default(false);
            $table->string('musical_instrument_1', 60)->nullable();
            $table->string('musical_instrument_2', 60)->nullable();
            $table->boolean('has_sports_equipment')->default(false);
            $table->string('sports_equipment_1', 60)->nullable();
            $table->string('sports_equipment_2', 60)->nullable();
            $table->timestamps();
        });

        Schema::create('student_family', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('relation_type', [
                'father',
                'mother',
                'guardian',
                'sibling',
                'step_sibling',
                'other_family',
            ]);
            $table->string('full_name', 120);
            $table->string('nik', 24)->nullable();
            $table->unsignedSmallInteger('birth_year')->nullable();
            $table->string('relationship_detail', 60)->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('religion', 20)->nullable();
            $table->string('occupation', 80)->nullable();
            $table->string('rank_group', 40)->nullable();
            $table->string('position_title', 80)->nullable();
            $table->string('education', 40)->nullable();
            $table->unsignedInteger('monthly_income')->nullable();
            $table->string('special_needs', 80)->nullable();
            $table->boolean('is_guardian')->default(false);
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('lives_with_student')->default(false);
            $table->string('marital_status', 30)->nullable();
            $table->text('address_text')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'relation_type']);
            $table->index(['student_id', 'is_guardian']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_family');
        Schema::dropIfExists('student_assets_home');
        Schema::dropIfExists('student_learning_profiles');
        Schema::dropIfExists('student_previous_schools');
        Schema::dropIfExists('student_medical_histories');
        Schema::dropIfExists('student_health');

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nickname',
                'class_name',
                'nisn',
                'nik_kitas',
                'family_card_number',
                'birth_place',
                'birth_date',
                'birth_certificate_number',
                'religion',
                'citizenship',
                'daily_language',
                'whatsapp_number',
                'email',
                'address_text',
                'notes',
            ]);
        });
    }
};
