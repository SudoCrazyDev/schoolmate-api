<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class InstitutionSection extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'class_adviser',
        'institution_id',
        'grade_level',
        'title',
        'academic_year'
    ];
    
    public function class_adviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_adviser', 'id');
    }
    
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
    
    public function subjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'section_id');
    }
    
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_sections', 'section_id', 'student_id');
    }
}
