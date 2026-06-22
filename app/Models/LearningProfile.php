<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'answers',          // JSON: {"q1":"visual","q2":"competitive",...}
        'score_visual',
        'score_auditory',
        'score_competitive',
        'confidence',       // float 0–100
        'learning_style',   // visual | auditory | competitive
        'persona',          // human-readable label, e.g. "Visual Learner"
        'recommendations',  // JSON array of recommendation strings
    ];

    protected $casts = [
        'answers'         => 'array',
        'recommendations' => 'array',
        'confidence'      => 'float',
    ];

    /**
     * Get the user that owns the learning profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

