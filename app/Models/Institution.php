<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Institution extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'title',
        'abbr',
        'address',
        'division',
        'region',
        'gov_id',
        'logo'
    ];
    
    protected $hidden = [
        'pivot'
    ];
    
    public function principal(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_institutions', 'institution_id', 'user_id')->whereHas('roles', function($query){
            $query->where('slug', 'principal');
        });
    }
    
    protected function logo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Storage::temporaryUrl($value, now()->addMinute()) : null
        );
    }
}
