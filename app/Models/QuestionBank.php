<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    use HasFactory;

    protected $table = 'question_bank';

    protected $fillable = [
        'question_text',
        'type',
        'options',
        'correct_answer',
        'topic',
        'difficulty',
        'points',
    ];

    protected $casts = [
        'options' => 'array',
    ];
}
