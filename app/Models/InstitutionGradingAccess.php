<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionGradingAccess extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'institution_id',
        'quarter_one',
        'quarter_two',
        'quarter_three',
        'quarter_four'
    ];
}
