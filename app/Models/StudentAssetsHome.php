<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAssetsHome extends Model
{
    protected $table = 'student_assets_home';

    protected $fillable = [
        'student_id',
        'home_to_school_distance_km',
        'home_to_school_travel_minutes',
        'transport_mode',
        'household_vehicle',
        'living_environment',
        'home_lighting_condition',
        'bedroom_condition',
        'study_room_condition',
        'learning_tools',
        'has_musical_instruments',
        'musical_instrument_1',
        'musical_instrument_2',
        'has_sports_equipment',
        'sports_equipment_1',
        'sports_equipment_2',
    ];

    protected function casts(): array
    {
        return [
            'home_to_school_distance_km' => 'decimal:2',
            'home_to_school_travel_minutes' => 'integer',
            'has_musical_instruments' => 'boolean',
            'has_sports_equipment' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
