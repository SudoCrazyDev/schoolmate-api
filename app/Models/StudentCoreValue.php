<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCoreValue extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'student_id',
        'academic_year',
        'quarter',
        'core_value',
        'remarks'
    ];
}
