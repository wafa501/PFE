<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookReactionPost extends Model
{
    protected $table = 'facebook_reactions_posts';


    protected $fillable = [
        'post_id',
        'like_count',
        'love_count',
        'wow_count',
        'haha_count',
        'sad_count',
        'angry_count',
        'total_reactions',
        'comments_count',
        'message_comments',
    ];
}
