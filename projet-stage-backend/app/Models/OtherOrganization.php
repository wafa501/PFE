<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherOrganization extends Model
{
    protected $fillable = [
        'vanity_name',
        'followers',
        'localized_name',
        'name',
        'primary_organization_type',
        'locations',
        'linkedin_id',
        'localized_website',
        'logo_v2',
        'paging',
        'is_active',
        'last_synced_at' 
    ];

    protected $casts = [
        'localized_name' => 'array',
        'name' => 'array',
        'locations' => 'array',
        'logo_v2' => 'array',
        'paging' => 'array',
        'is_active' => 'boolean', 
        'last_synced_at' => 'datetime' 
    ];

    // Relation avec les posts
    public function posts()
    {
        return $this->hasMany(Posts::class, 'author', 'linkedin_id');
    }
}