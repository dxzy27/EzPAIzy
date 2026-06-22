<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashcardProgress extends Model
{
    use HasFactory;

    protected $table = 'flashcard_progress';

    protected $fillable = [
        'user_id',
        'flashcard_id',
        'status',
        'ease_factor',
        'interval',
        'repetitions',
        'next_review_date',
    ];

    protected $casts = [
        'next_review_date' => 'datetime',
        'ease_factor' => 'float',
        'interval' => 'integer',
        'repetitions' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flashcard()
    {
        return $this->belongsTo(Flashcard::class);
    }
}
