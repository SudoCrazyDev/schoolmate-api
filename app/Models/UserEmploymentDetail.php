<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmploymentDetail extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'user_id',
        'employee_id',
        'date_started',
        'position',
    ];
}
