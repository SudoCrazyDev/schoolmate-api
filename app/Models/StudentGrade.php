<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGrade extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'student_id',
        'subject_id',
        'quarter',
        'grade',
        'is_locked'
    ];
    
    public function subject(): BelongsTo
    {
        return $this->belongsTo(SectionSubject::class, 'subject_id');
    }
}
