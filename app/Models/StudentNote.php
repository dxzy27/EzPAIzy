<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'topic',
        'difficulty',
        'title',
        'content',
        'resource_type',
        'resource_id',
    ];

    /**
     * Get the student that owns the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
