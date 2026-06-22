<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    
    protected $fillable = ['title', 'teacher_id', 'difficulty', 'topic', 'is_flagged'];
    
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    
    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
