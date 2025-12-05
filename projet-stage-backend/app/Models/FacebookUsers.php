<?php


namespace App\Models;
use Illuminate\Support\Facades\Log;

use Illuminate\Foundation\Auth\User as Authenticatable;

class FacebookUsers extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'facebook_id',
        'access_token',
    ];
    public function getAuthIdentifierName()
    {
        return 'id'; 
    }

    public function getAuthIdentifier()
    {
        return $this->getAttribute($this->getAuthIdentifierName());
    }

    public function getRememberToken()
    {
        return $this->remember_token; 
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

     /**
     * Retrieve the access token for a given Facebook ID.
     *
     * @param  string  $facebookId
     * @return string|null
     */
    public static function getAccessTokenByFacebookId($facebookId)
    {
        $user = self::where('facebook_id', $facebookId)->first();

        return $user ? $user->access_token : null;
    }

      /**
     * Retrieve the access token for a given Facebook ID.
     *
     * @param  string  $facebookId
     * @return string|null
     */
    public static function getPageAccessTokenByFacebookId($facebookId, $pageId)
    {
        $user = self::where('facebook_id', $facebookId)->first(); 
    
        if (!$user) {
            Log::warning("User not found for Facebook ID: {$facebookId}");
            return null; 
        }
    
        $page = FacebookPage::where('facebook_user_id', $user->id) 
                    ->where('page_id', $pageId) 
                    ->first();
    
        if ($page && !empty($page->page_access_token)) {
            return $page->page_access_token;
        }
    
        Log::warning("Access token not found for Facebook ID: {$facebookId}, Page ID: {$pageId}");
    
        return null; 
    }
    
    
/**
 * Retrieve the pages managed by the connected user.
 *
 * @param string $facebookId
 * @return \Illuminate\Database\Eloquent\Collection
 */
public static function getPagesByFacebookId($facebookId)
{
    $user = self::where('facebook_id', $facebookId)->first();

    if ($user) {
        return FacebookPage::where('facebook_user_id', $user->id)->get();
    }

    return collect();
}



}
