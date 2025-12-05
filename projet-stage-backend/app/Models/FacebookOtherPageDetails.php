<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookOtherPageDetails extends Model
{
    protected $table = '_facebook__otherpages_details_';

    protected $fillable = [
        'id', 'name', 'location', 'link', 'about', 'category', 'picture', 'fan_count', 'website'
    ];

    public $incrementing = false;
}
