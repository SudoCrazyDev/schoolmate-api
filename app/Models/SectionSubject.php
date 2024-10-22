<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SectionSubject extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'subject_teacher',
        'parent_subject',
        'section_id',
        'sem',
        'title',
        'start_time',
        'end_time',
        'schedule'
    ];
    
    public function section(): BelongsTo
    {
        return $this->belongsTo(InstitutionSection::class, 'section_id');
    }
    
    public function subject_teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_teacher');
    }
    
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_teacher');
    }
    
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_sections', 'section_id', 'student_id', 'section_id', 'id');
    }
    
    public function sub_subjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'parent_subject');
    }
    
}
