<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookPage extends Model
{
    protected $fillable = [
        'facebook_user_id', 
        'page_id',
        'page_access_token',
    ];

    public function user()
    {
        return $this->belongsTo(FacebookUsers::class);
    }

    public function getPageDetails($pageId, $accessToken)
    {
        $url = "https://graph.facebook.com/v17.0/{$pageId}?fields=id,name";
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        return null; 
    }
}
