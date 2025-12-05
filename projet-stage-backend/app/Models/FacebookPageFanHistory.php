<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookPageFanHistory extends Model
{
    protected $table = 'facebook_page_fan_history'; 

    public $timestamps = false; 

    protected $fillable = ['page_id', 'fan_count', 'checked_at'];
}


