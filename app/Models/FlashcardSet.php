<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashcardSet extends Model
{
    protected $fillable = ['title', 'description', 'user_id', 'is_public', 'topic', 'is_flagged'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flashcards()
    {
        return $this->hasMany(Flashcard::class)->orderBy('position');
    }
}
