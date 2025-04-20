<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'institution_id',
        'employee_id',
        'employee',
        'status',
        'date_time',
        'auth_date',
        'auth_time'
    ];
}
