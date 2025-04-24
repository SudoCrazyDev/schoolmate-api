<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionSubscription extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'institution_id',
        'subscription_id',
        'start_date',
        'end_date',
        'is_active',
        'is_trial',
        'trial_days',
    ];
    
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
