<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'institution_id',
        'lrn',
        'first_name',
        'middle_name',
        'last_name',
        'ext_name',
        'gender',
        'birthdate'
    ];
    
    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'student_id');
    }
    
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(InstitutionSection::class, 'student_sections', 'student_id', 'section_id');
    }
}
