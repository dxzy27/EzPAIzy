<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Teacher extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',        // optional: if you want to store the teacher's phone number
        'address',      // optional: if you want to store the teacher's address
        'profile_image', // optional: if you want to store the teacher's profile image
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Example of relationship: A teacher can have many contents (lessons, quizzes, etc.)
    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    // Example of relationship: A teacher can create many quizzes
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

}
