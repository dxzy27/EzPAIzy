<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'phone_number',
        'address',
        'role',
        'class_name',
        'is_suspended',
        'is_approved',
        'learning_style',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a teacher.
     *
     * @return bool
     */
    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if the user is a student.
     *
     * @return bool
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * Assign a role to the user.
     * Only allow during user creation or by explicit admin action.
     *
     * @param string $role
     * @return void
     */
    public function assignRole($role)
    {
        if (!in_array($role, ['admin', 'teacher', 'student'])) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }
        
        $this->role = $role;
        $this->save();
    }

    /**
     * Promote a student to teacher (admin only).
     * This should only be called after proper authorization.
     *
     * @return void
     */
    public function promoteToTeacher()
    {
        if ($this->isTeacher()) {
            return;
        }
        
        $this->assignRole('teacher');
    }

    /**
     * Demote a teacher to student (admin only).
     * This should only be called after proper authorization.
     *
     * @return void
     */
    public function demoteToStudent()
    {
        if ($this->isStudent()) {
            return;
        }
        
        $this->assignRole('student');
    }
    
    /**
     * A student can have many progress records.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function progress()
    {
        return $this->hasMany(Progress::class, 'student_id');
    }
    
    /**
     * A teacher can have many quizzes.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'teacher_id');
    }
    
    /**
     * A teacher can have many contents.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contents()
    {
        return $this->hasMany(Content::class, 'teacher_id');
    }

    /**
     * A user can have many widgets on their dashboard.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function widgets()
    {
        return $this->hasMany(Widget::class)->orderBy('position');
    }
    
    /**
     * A student can have many favorited contents.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favorites()
    {
        return $this->belongsToMany(Content::class, 'favorites', 'student_id', 'content_id')
            ->withTimestamps();
    }

    /**
     * A teacher can have many flashcard sets.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function flashcardSets()
    {
        return $this->hasMany(FlashcardSet::class);
    }

    /**
     * A teacher can create many custom topics.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Get a consistent random color for the user avatar based on their ID.
     *
     * @return string
     */
    public function getAvatarColorAttribute()
    {
        $colors = [
            '#ef4444', // Red 500
            '#f97316', // Orange 500
            '#f59e0b', // Amber 500
            '#84cc16', // Lime 500
            '#22c55e', // Green 500
            '#10b981', // Emerald 500
            '#14b8a6', // Teal 500
            '#06b6d4', // Cyan 500
            '#0ea5e9', // Sky 500
            '#3b82f6', // Blue 500
            '#6366f1', // Indigo 500
            '#8b5cf6', // Violet 500
            '#a855f7', // Purple 500
            '#d946ef', // Fuchsia 500
            '#ec4899', // Pink 500
            '#f43f5e', // Rose 500
        ];

        // Use the user ID to consistently pick the same color for the same user
        // We use abs(crc32($this->id)) to ensure integer handling if ID is UUID in future (though currently int)
        // or just simpler: $this->id % count($colors)
        
        $index = $this->id % count($colors);
        
        return $colors[$index];
    }

    /**
     * Mutator to keep 'phone' and 'phone_number' fields synchronized.
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $value;
        $this->attributes['phone_number'] = $value;
    }

    /**
     * Mutator to keep 'phone_number' and 'phone' fields synchronized.
     */
    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = $value;
        $this->attributes['phone'] = $value;
    }
}
