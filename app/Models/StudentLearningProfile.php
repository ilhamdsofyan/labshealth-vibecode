<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLearningProfile extends Model
{
    protected $fillable = [
        'student_id',
        'sports_hobby',
        'arts_hobby',
        'other_hobby',
        'talent_field',
        'has_leisure_time',
        'reading_start_age_months',
        'writing_start_age_months',
        'counting_start_age_months',
        'speaking_start_age_months',
        'start_kb_tk_age_months',
        'start_sd_age_months',
        'start_smp_age_months',
        'likes_school',
        'likes_play_with',
        'likes_game_type',
        'preferred_activity',
        'concentration_level',
        'task_completion_style',
        'imagination_role',
        'has_home_study_group',
        'study_group_beneficial',
        'attends_tutoring',
        'tutoring_institution',
        'self_study_hours_per_day',
        'has_home_study_schedule',
        'common_study_time',
        'asks_curiosity_questions',
        'curiosity_topics',
    ];

    protected function casts(): array
    {
        return [
            'has_leisure_time' => 'boolean',
            'reading_start_age_months' => 'integer',
            'writing_start_age_months' => 'integer',
            'counting_start_age_months' => 'integer',
            'speaking_start_age_months' => 'integer',
            'start_kb_tk_age_months' => 'integer',
            'start_sd_age_months' => 'integer',
            'start_smp_age_months' => 'integer',
            'likes_school' => 'boolean',
            'has_home_study_group' => 'boolean',
            'study_group_beneficial' => 'boolean',
            'attends_tutoring' => 'boolean',
            'self_study_hours_per_day' => 'decimal:2',
            'has_home_study_schedule' => 'boolean',
            'asks_curiosity_questions' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
