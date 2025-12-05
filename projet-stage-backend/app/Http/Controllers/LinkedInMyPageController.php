<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Organization;
use App\Models\LinkedInToken;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 

class LinkedInMyPageController extends Controller
{
    public function getPageData($pageId)
    {
        $userId = Auth::id(); 
        $tokenRecord = LinkedInToken::where('user_id', $userId)->first(); 
        $accessToken = $tokenRecord ? $tokenRecord->access_token : null;

        if (!$accessToken) {
            Log::error('Error: Missing access token for user ID: ' . $userId);
            return redirect()->route('home')->withErrors('Error: Missing access token.');
        }

        Log::info('Access token for user ID ' . $userId . ': ' . $accessToken);

        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
        ])->get("https://api.linkedin.com/v2/organizations/{$pageId}");

        $responseFollowers = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'LinkedIn-Version' => "202307",
        ])->get("https://api.linkedin.com/rest/networkSizes/urn:li:organization:{$pageId}?edgeType=COMPANY_FOLLOWED_BY_MEMBER");

        Log::info('Page data response: ' . $response->body());
        Log::info('Followers response: ' . $responseFollowers->body());

        if ($response->failed()) {
            Log::error('Error: Failed to retrieve LinkedIn page data for user ID: ' . $userId);
            return redirect()->route('home')->withErrors('Error: Failed to retrieve LinkedIn page data.');
        }

        if ($responseFollowers->failed()) {
            Log::error('Error: Failed to retrieve LinkedIn followers data for user ID: ' . $userId);
            return redirect()->route('home')->withErrors('Error: Failed to retrieve LinkedIn followers data.');
        }

        $pageData = $response->json();
        $pageFollowers = $responseFollowers->json();
        
        Log::info('Followers count for user ID ' . $userId . ': ' . $pageFollowers['firstDegreeSize']);

        if (!is_array($pageData)) {
            Log::error('Error: Invalid LinkedIn page data format for user ID: ' . $userId);
            return redirect()->route('home')->withErrors('Error: Invalid LinkedIn page data format.');
        }

        Log::info('LinkedIn Page Data for user ID ' . $userId . ':', $pageData);

        // Store the organization data associated with the authenticated user
        Organization::updateOrCreate(
            [
                'organization' => $pageData['$URN'],
                'user_id' => $userId // Assuming Organization model has a user_id field
            ],
            [
                'vanity_name' => $pageData['vanityName'] ?? null,
                'followers' => $pageFollowers['firstDegreeSize'] ?? null,
                'localized_name' => $pageData['localizedName'] ?? null,
                'groups' => $pageData['groups'] ?? [],
                'version_tag' => $pageData['versionTag'] ?? null,
                'organization_type' => $pageData['organizationType'] ?? null,
                'default_locale' => $pageData['defaultLocale'] ?? null,
                'alternative_names' => $pageData['alternativeNames'] ?? [],
                'specialties' => $pageData['specialties'] ?? [],
                'staff_count_range' => $pageData['staffCountRange'] ?? null,
                'localized_specialties' => $pageData['localizedSpecialties'] ?? [],
                'industries' => $pageData['industries'] ?? [],
                'name' => $pageData['name'] ?? null,
                'primary_organization_type' => $pageData['primaryOrganizationType'] ?? null,
                'locations' => $pageData['locations'] ?? [],
                'linkedin_id' => $pageData['id'],
            ]
        );

        // Fetch additional statistics
        $allStatistics = $this->fetchStatistics($accessToken, $pageId);

        foreach ($allStatistics as $stat) {
            $organization = data_get($stat, 'organization');
            Organization::updateOrCreate(
                [
                    'organization' => $organization,
                    'user_id' => $userId 
                ],
                [
                    'page_statistics_by_seniority' => json_encode(data_get($stat, 'pageStatisticsBySeniority', [])),
                    'page_statistics_by_country' => json_encode(data_get($stat, 'pageStatisticsByCountry', [])),
                    'page_statistics_by_industry' => json_encode(data_get($stat, 'pageStatisticsByIndustry', [])),
                    'page_statistics_by_targeted_content' => json_encode(data_get($stat, 'pageStatisticsByTargetedContent', [])),
                    'total_page_statistics' => json_encode(data_get($stat, 'totalPageStatistics', [])),
                    'page_statistics_by_staff_count_range' => json_encode(data_get($stat, 'pageStatisticsByStaffCountRange', [])),
                    'page_statistics_by_function' => json_encode(data_get($stat, 'pageStatisticsByFunction', [])),
                    'page_statistics_by_region' => json_encode(data_get($stat, 'pageStatisticsByRegion', [])),
                ]
            );
        }
       
        return view('page', ['pageData' => $pageData]);
    }

    private function fetchStatistics($accessToken, $pageId)
    {
        $allStatistics = [];
        $url = "https://api.linkedin.com/v2/organizationPageStatistics?q=organization&organization=urn:li:organization:{$pageId}";
    
        while ($url) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'LinkedIn-Version' => "202307",
            ])->get($url);
    
            if ($response->failed()) {
                Log::error('Error: Failed to retrieve LinkedIn stats data. Response: ' . $response->body());
                return $allStatistics;
            }
    
            $data = $response->json();
            $statistics = $data['elements'] ?? [];
            $allStatistics = array_merge($allStatistics, $statistics);
    
            $paging = $data['paging'] ?? [];
            Log::info('Paging Data', ['paging' => $paging]);
    
            break; 
        }
    
        return $allStatistics;
    }
    

    // public function getDataFromDatabase()
    // {
    //         $userId = Auth::id();
    // Log::info("userid ".$userId);
    //     $organizations = Organization::where('user_id', $userId)->get();
    
    //     if ($organizations->isEmpty()) {
    //         Log::error('Error: No organizations found for user ID: ' . $userId);
    //         return response()->json(['error' => 'No organizations found.'], 404);
    //     }
    //     Log::info('Organizations data for user ID ' . $userId . ':', $organizations->toArray());
    //     return response()->json($organizations);
        
    // }
    
    public function getDataFromDatabase($pageId)
    {
        $userId = Auth::id();
        Log::info("userid ".$userId);
        
        $organization = Organization::where('user_id', $userId)
            ->where('linkedin_id', $pageId) 
            ->first();

        if (!$organization) {
            Log::error('Error: Organization not found for user ID: ' . $userId . ' and page ID: ' . $pageId);
            return response()->json(['error' => 'Organization not found.'], 404);
        }
        
        Log::info('Organization data:', $organization->toArray());
        return response()->json($organization);
    }

    public function getUserOrganizations($accessToken)
{
    $userId = Auth::id();
    Log::info("userid1".$userId);

    if (!$accessToken) {
        return redirect()->route('home')->withErrors('Error: Missing access token.');
    }

    $response = Http::withHeaders([
        'Authorization' => "Bearer $accessToken",
        'LinkedIn-Version' => "202307",
    ])->get('https://api.linkedin.com/v2/organizationAcls?q=roleAssignee&role=ADMINISTRATOR&state=APPROVED');

    if ($response->failed()) {
        return response()->json(['error' => 'Failed to retrieve admin pages'], 500);
    }
    
    Log::info("1111111111111111");
    $organizations = $response->json('elements');
    if (isset($stat['organization'])) {
    $organization = $stat['organization'];
} else {
    Log::error('Organization not found in statistics');
}

    $organizationData = [];

    foreach ($organizations as $org) {
        $organizationUrn = $org['organization'];
        $organizationId = str_replace('urn:li:organization:', '', $organizationUrn);

        $organizationResponse = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
        ])->get("https://api.linkedin.com/v2/organizations/{$organizationId}");

        Log::info("2222222222222222222222222");
        Log::info("organizationId".$organizationId);
         $responseFollowers = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'LinkedIn-Version' => "202307",
        ])->get("https://api.linkedin.com/rest/networkSizes/urn:li:organization:{$organizationId}?edgeType=COMPANY_FOLLOWED_BY_MEMBER");

        if ($responseFollowers->failed()) {
            Log::error('Error: Failed to retrieve LinkedIn followers data for user ID: ' . $userId);
            return redirect()->route('home')->withErrors('Error: Failed to retrieve LinkedIn followers data.');
        }
        $pageFollowers = $responseFollowers->json();


        if ($organizationResponse->failed()) {
            Log::error('Error: Failed to retrieve LinkedIn followers data for user ID: ' . $userId);
            return redirect()->route('home')->withErrors('Error: Failed to retrieve LinkedIn followers data.');
        }

        Organization::updateOrCreate(
            [
                'organization' => $organizationResponse['$URN'],
                'user_id' => $userId 
            ],
            [
                'vanity_name' => $organizationResponse['vanityName'] ?? null,
                'followers' => $pageFollowers['firstDegreeSize'] ?? null,
                'localized_name' => $organizationResponse['localizedName'] ?? null,
                'groups' => $organizationResponse['groups'] ?? [],
                'version_tag' => $organizationResponse['versionTag'] ?? null,
                'organization_type' => $organizationResponse['organizationType'] ?? null,
                'default_locale' => $organizationResponse['defaultLocale'] ?? null,
                'alternative_names' => $organizationResponse['alternativeNames'] ?? [],
                'specialties' => $organizationResponse['specialties'] ?? [],
                'staff_count_range' => $organizationResponse['staffCountRange'] ?? null,
                'localized_specialties' => $organizationResponse['localizedSpecialties'] ?? [],
                'industries' => $organizationResponse['industries'] ?? [],
                'name' => $organizationResponse['name'] ?? null,
                'primary_organization_type' => $organizationResponse['primaryOrganizationType'] ?? null,
                'locations' => $organizationResponse['locations'] ?? [],
                'linkedin_id' => $organizationResponse['id'],
            ]
        );
    }

    return response()->json($organizationData);
}


public function UpdateMyStatsOrganizations()
{
    $userId = Auth::id();
    Log::info("userid1".$userId);
    $user = User::find($userId);
    $accessToken = $user->linkedin_token ?? null; 


    if (!$accessToken) {
        return redirect()->route('home')->withErrors('Error: Missing access token.');
    }

    $response = Http::withHeaders([
        'Authorization' => "Bearer $accessToken",
        'LinkedIn-Version' => "202307",
    ])->get('https://api.linkedin.com/v2/organizationAcls?q=roleAssignee&role=ADMINISTRATOR&state=APPROVED');

    if ($response->failed()) {
        return response()->json(['error' => 'Failed to retrieve admin pages'], 500);
    }
    
    //Log::info("1111111111111111");
    $organizations = $response->json('elements');
    $organizationData = [];


    foreach ($organizations as $org) {
        $organizationUrn = $org['organization'];
        $organizationId = str_replace('urn:li:organization:', '', $organizationUrn);
        $allStatistics = $this->fetchStatistics($accessToken, $organizationId);

    foreach ($allStatistics as $stat) {
        $organization = data_get($stat, 'organization');
        Organization::updateOrCreate(
            [
                'organization' => $organization,
                'user_id' => $userId 
            ],
            [
                'page_statistics_by_seniority' => json_encode(data_get($stat, 'pageStatisticsBySeniority', [])),
                'page_statistics_by_country' => json_encode(data_get($stat, 'pageStatisticsByCountry', [])),
                'page_statistics_by_industry' => json_encode(data_get($stat, 'pageStatisticsByIndustry', [])),
                'page_statistics_by_targeted_content' => json_encode(data_get($stat, 'pageStatisticsByTargetedContent', [])),
                'total_page_statistics' => json_encode(data_get($stat, 'totalPageStatistics', [])),
                'page_statistics_by_staff_count_range' => json_encode(data_get($stat, 'pageStatisticsByStaffCountRange', [])),
                'page_statistics_by_function' => json_encode(data_get($stat, 'pageStatisticsByFunction', [])),
                'page_statistics_by_region' => json_encode(data_get($stat, 'pageStatisticsByRegion', [])),
            ]
        );
    }
}
    return view('page', ['pageData' => $stat]);

}
}
