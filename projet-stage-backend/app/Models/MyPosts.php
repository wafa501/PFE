<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyPosts extends Model
{
    protected $table = 'myposts';

    protected $fillable = [
        'idPost',
        'published_at',
        'last_modified_at',
        'lifecycle_state',
        'visibility',
        'distribution',
        'content',
        'commentary',
        'alt_text',
        'image_url',
        'video_url',
        'likes',
        'comments',
        'numberLikes',
        'numberComments',
        'CommentsList',
        'page_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'published_at' => 'datetime',
        'last_modified_at' => 'datetime',
        'distribution' => 'array',
        'content' => 'array',
        'likes' => 'array', 
        'comments' => 'array',
        'CommentsList' => 'array',
    ];
}
