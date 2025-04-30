<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'ext_name',
        'gender',
        'birthdate',
        'email',
        'password',
        'token',
        'is_new'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Hash::make($value),
        );
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }
    
    public function personal_details(): HasOne
    {
        return $this->hasOne('user_personal_details');
    }

    public function family_background(): HasOne
    {
        return $this->hasOne('user_family_background');
    }
    
    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'user_institutions', 'user_id', 'institution_id');
    }
    
    public function loads(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'subject_teacher');
    }
    
    public function advisory(): HasOne
    {
        return $this->hasOne(InstitutionSection::class, 'class_adviser');
    }
    
    public function employment(): HasOne
    {
        return $this->hasOne(UserEmploymentDetail::class, 'user_id');
    }
    
    public function custom_attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'employee_id', 'id');
    }
    
    public function proper_attendances(): HasManyThrough
    {
        return $this->hasManyThrough(TeacherAttendance::class, UserEmploymentDetail::class, 'user_id', 'employee_id', 'id', 'employee_id');
    }
}
