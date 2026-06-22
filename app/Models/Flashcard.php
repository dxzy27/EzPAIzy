<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    protected $fillable = ['flashcard_set_id', 'term', 'definition', 'image_path', 'position'];

    public function flashcardSet()
    {
        return $this->belongsTo(FlashcardSet::class);
    }
}
