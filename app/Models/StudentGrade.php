<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
