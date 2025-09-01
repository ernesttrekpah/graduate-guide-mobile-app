<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'password'          => 'hashed',
        ];
    }

    public function roles()
    {
        // Your custom pivot
        return $this->belongsToMany(\App\Models\Role::class, 'role_user');
    }

    public function consents()
    {return $this->hasMany(Consent::class);}

    public function profile()
    {return $this->hasOne(StudentProfile::class);}

    // Saved programmes (pivot with timestamps + note)
    public function savedProgrammes()
    {
        return $this->belongsToMany(Programme::class, 'saved_programmes')
            ->withPivot('note')
            ->withTimestamps();
    }

// Feedback
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

}
