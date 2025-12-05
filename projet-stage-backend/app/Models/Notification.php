<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'post_id',
        'message',
        'is_read',
        'videoUrl',
        'ImageUrl', 
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
