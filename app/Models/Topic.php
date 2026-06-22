<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'is_system',
        'type',
    ];

    /**
     * Get the user who created this topic (null for system topics).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
