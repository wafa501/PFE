<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    protected $table = 'statistics';

    protected $fillable = [
        'organization',
        'year',
        'monthly_stats',
    ];

    protected $casts = [
        'monthly_stats' => 'array',
    ];
}

