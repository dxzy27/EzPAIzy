<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;
    
    protected $fillable = ['student_id', 'quiz_id', 'score', 'student_answers', 'status', 'teacher_notes'];
    
    protected $casts = [
        'student_answers' => 'array',
        'teacher_notes' => 'array',
    ];
    
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
