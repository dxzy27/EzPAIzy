<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;
    
    protected $fillable = ['title', 'content', 'teacher_id', 'topic', 'file_path', 'file_type', 'is_flagged'];
    
    
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    
    /**
     * Students who favorited this content
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'content_id', 'student_id')
            ->withTimestamps();
    }
}