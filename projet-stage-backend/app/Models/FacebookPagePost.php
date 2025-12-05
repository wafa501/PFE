<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookPagePost extends Model
{
    protected $fillable = [
        'fb_id', 'created_time', 'updated_time', 'status_type',
        'attachments', 'privacy', 'pictures' , 'videos' , 'description'
    ];

}
