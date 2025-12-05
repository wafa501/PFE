<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'my_organization';

    protected $fillable = [
        'organization',
        'user_id',
        'vanity_name',
        'followers',
        'localized_name',
        'groups',
        'version_tag',
        'organization_type',
        'default_locale',
        'alternative_names',
        'specialties',
        'staff_count_range',
        'localized_specialties',
        'industries',
        'name',
        'primary_organization_type',
        'locations',
        'linkedin_id',
        'page_statistics_by_seniority',
        'page_statistics_by_country',
        'page_statistics_by_industry',
        'page_statistics_by_targeted_content',
        'total_page_statistics',
        'page_statistics_by_staff_count_range',
        'page_statistics_by_function',
        'page_statistics_by_region',
    ];

    protected $casts = [
        'groups' => 'array',
        'default_locale' => 'array',
        'alternative_names' => 'array',
        'specialties' => 'array',
        'localized_specialties' => 'array',
        'industries' => 'array',
        'name' => 'array',
        'locations' => 'array',
        'page_statistics_by_seniority' => 'array',
        'page_statistics_by_country' => 'array',
        'page_statistics_by_industry' => 'array',
        'page_statistics_by_targeted_content' => 'array',
        'total_page_statistics' => 'array',
        'page_statistics_by_staff_count_range' => 'array',
        'page_statistics_by_function' => 'array',
        'page_statistics_by_region' => 'array',
    ];
}
