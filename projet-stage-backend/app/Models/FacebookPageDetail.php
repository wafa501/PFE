<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookPageDetail extends Model
{

    protected $table = 'facebook_page_details';

    protected $fillable = [
        'fb_id', 'name', 'fan_count', 'about', 'category', 'website',
        'phone', 'price_range', 'mission', 'products', 'hours', 'location'
    ];

    protected $casts = [
        'products' => 'array',
        'hours' => 'array',
        'location' => 'array',
    ];

    public $timestamps = true;
}
