<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookMetric extends Model
{
    protected $fillable = [
        'name', 'title', 'description', 'period', 'fb_id', 'value', 'end_time'
    ];
}
