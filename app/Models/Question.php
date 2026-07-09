<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['question_text', 'type', 'options', 'correct_answer', 'points', 'topic', 'difficulty', 'is_flagged'];

    protected $casts = [
        'options' => 'array',
    ];
}
