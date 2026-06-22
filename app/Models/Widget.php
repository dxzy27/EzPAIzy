<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $fillable = ['user_id', 'widget_type', 'position', 'settings'];

    protected $casts = [
        'settings' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
