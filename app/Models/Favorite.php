<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;
    
    protected $fillable = ['student_id', 'content_id', 'flashcard_set_id'];
    
    /**
     * Get the student who favorited this content
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    
    /**
     * Get the content that was favorited
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get the flashcard set that was favorited
     */
    public function flashcardSet()
    {
        return $this->belongsTo(FlashcardSet::class);
    }
}
