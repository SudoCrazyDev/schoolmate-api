<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionTimeSchedule extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'title',
        'institution_id',
        'start_working_time',
        'end_working_time',
        'early_time_in',
        'late_time_in',
        'break_in',
        'break_out',
        'valid_check_out',
        'late_check_out',
        'color'
    ];
}
