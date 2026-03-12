<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Visit extends Model
{
    use SoftDeletes, Auditable;
    protected $fillable = [
        'visit_date',
        'visit_time',
        'patient_name',
        'gender',
        'patient_category',
        'class_or_department',
        'complaint',
        'disease_id',
        'medication_id',
        'student_id',
        'employee_id',
        'therapy',
        'officer_name',
        'notes',
        'visit_type',
        'is_acc_pulang',
        'is_rest',
        'bed_id',
        'acc_pulang_reason',
        'class_at_visit',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'is_acc_pulang' => 'boolean',
            'is_rest' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(Disease::class)->withTimestamps();
    }

    public function medications(): BelongsToMany
    {
        return $this->belongsToMany(Medication::class)->withTimestamps();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function scopeFilterByDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('visit_date', [$startDate, $endDate]);
        }
        if ($startDate) {
            return $query->where('visit_date', '>=', $startDate);
        }
        if ($endDate) {
            return $query->where('visit_date', '<=', $endDate);
        }
        return $query;
    }

    public function scopeFilterByCategory($query, $category)
    {
        if ($category) {
            return $query->where('patient_category', $category);
        }
        return $query;
    }

    public function scopeFilterByClass($query, $class)
    {
        if ($class) {
            return $query->where('class_or_department', 'like', "%{$class}%");
        }
        return $query;
    }

    public function scopeFilterByComplaint($query, $complaint)
    {
        if ($complaint) {
            return $query->where('complaint', 'like', "%{$complaint}%");
        }
        return $query;
    }

    public function scopeFilterByType($query, $type)
    {
        if ($type) {
            return $query->where('visit_type', $type);
        }
        return $query;
    }
}
