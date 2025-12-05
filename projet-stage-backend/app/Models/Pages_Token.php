<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pages_Token extends Model
{
    protected $fillable = [
        'name',
        'email',
        'facebook_id',
        'access_token',
        'password'
    ];

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
}
