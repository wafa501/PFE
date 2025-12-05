<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    protected $fillable = [
        'idPost',
        'author',
        'published_at',
        'last_modified_at',
        'lifecycle_state',
        'visibility',
        'distribution',
        'content',
        'likes_count',
        'Comments_count',
        'uniqueImpressionsCount',
        'commentary',
        'alt_text',
        'image_url',
        'video_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'published_at' => 'datetime',
        'last_modified_at' => 'datetime',
        'distribution' => 'array',
        'content' => 'array',
    ];
}
