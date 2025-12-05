<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable 
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'given_name',
        'family_name',
        'locale',
        'picture',
        'email_verified',
        'localizedHeadline',
        'linkedin_token',
         'role',
        'blocked',

    ];

    protected $casts = [
        'locale' => 'array',
        'email_verified' => 'boolean',
        'blocked' => 'boolean',
    ];
}
