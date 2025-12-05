<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryStats extends Model
{
    protected $table = 'Summary_statistics_otherOrganizations';

    protected $fillable = [
        'organizationalEntity',
        'uniqueImpressionsCount',
        'shareCount',
        'shareMentionsCount',
        'engagement',
        'clickCount',
        'likeCount',
        'impressionCount',
        'commentMentionsCount',
        'commentCount',
    ];
  
}
