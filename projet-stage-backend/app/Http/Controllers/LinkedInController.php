<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User; 
use App\Models\LinkedInToken;
use Illuminate\Support\Facades\Auth;
$accessToken=""; 

class LinkedInController extends Controller
{ 

    public function redirectToLinkedIn()
    {
        $client_id = config('services.linkedin.client_id');
        $redirect_uri = urlencode(config('services.linkedin.redirect'));
        $scope = 'openid%20profile%20email%20w_member_social%20r_organization_social%20rw_organization_admin%20w_organization_social%20rw_ads%20r_organization_admin%20r_basicprofile';
        $response_type = 'code';

        $url = "https://www.linkedin.com/oauth/v2/authorization?response_type=$response_type&client_id=$client_id&redirect_uri=$redirect_uri&scope=$scope";

        Log::info('Redirecting to LinkedIn: ' . $url);

        return redirect($url);
    }

    public function handleLinkedInCallback(Request $request)
{
    if ($request->has('error')) {
        $error = $request->input('error');
        $errorDescription = $request->input('error_description');
        
        Log::error("LinkedIn authorization error: {$error} - {$errorDescription}");
        
        return redirect()->route('home')->withErrors("LinkedIn authorization error: {$error} - {$errorDescription}");
    }

    $code = $request->input('code');
    
    Log::info('Authorization code received: ' . $code);
    
    if (!$code) {
        Log::error('Error: Missing authorization code.');
        return redirect()->route('home')->withErrors('Error: Missing authorization code.');
    }

    $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => config('services.linkedin.redirect'),
        'client_id' => config('services.linkedin.client_id'),
        'client_secret' => config('services.linkedin.client_secret'),
    ]);

    Log::info('Access token response: ' . $response->body());

    if ($response->failed()) {
        Log::error('Error: Failed to retrieve access token.');
        return redirect()->route('home')->withErrors('Error: Failed to retrieve access token.');
    }

    $accessToken = $response->json('access_token');

    Log::info('Access token received: ' . $accessToken);
    //************************************************* */
    $profileData = $this->getLinkedInProfileData($accessToken);
    
    $user = User::updateOrCreate(
        ['email' => $profileData['email']], 
        [
            'name' => $profileData['name'],
            'given_name' => $profileData['given_name'] ?? null,
            'family_name' => $profileData['family_name'] ?? null,
            'locale' => $profileData['locale'] ?? [],
            'picture' => $profileData['picture'] ?? null,
            'email_verified' => $profileData['email_verified'] ?? false,
            'localizedHeadline' => $profileData['localizedHeadline'] ?? false,
            'linkedin_token' => $accessToken ?? null,
        ]
    );
     
    //*************************************************
    LinkedInToken::updateOrCreate(
        ['id' => 1], 
        ['access_token' => $accessToken]
    );

    session(['linkedin_access_token' => $accessToken]);
    Log::info('Access token stored in session: ' . session('linkedin_access_token'));
    Auth::login($user); 

    return redirect('http://localhost:3000/dashboard');
    //return $this->fetchLinkedInProfile();
}

private function getLinkedInProfileData($accessToken)
    {
        $profileResponse = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
        ])->get('https://api.linkedin.com/v2/userinfo');

        if ($profileResponse->failed()) {
            Log::error('Error: Failed to retrieve LinkedIn profile.');
            return response()->json(['error' => 'Failed to retrieve LinkedIn profile'], 500);
        }

        return $profileResponse->json();
    }


    private function fetchLinkedInProfile()
    {
        $accessToken = session('linkedin_access_token');

        if (!$accessToken) {
            Log::error('Error: Missing access token in session.');
            return redirect()->route('home')->withErrors('Error: Missing access token.');
        }

        return redirect('http://localhost:3000/dashboard');
    }
    public function showPageOrganisations()
    {
        return view('infoOrganisation');
    }
    public function showPageAutreOrganisations()
    {
        return view('autreOrganisations');
    }
    // public function getProfileData()
    // {
    //     $accessToken = session('linkedin_access_token');
    
    //     if (!$accessToken) {
    //         $linkedInToken = LinkedInToken::find(1);  
    //         if ($linkedInToken && $linkedInToken->access_token) {
    //             $accessToken = $linkedInToken->access_token;
    //             session(['linkedin_access_token' => $accessToken]); 
    //         } else {
    //             return response()->json(['error' => 'Access token not found'], 401);
    //         }
    //     }
    
    //     $profileResponse = Http::withHeaders([
    //         'Authorization' => "Bearer {$accessToken}",
    //     ])->get('https://api.linkedin.com/v2/userinfo');

    //     $profile2Response = Http::withHeaders([
    //         'Authorization' => "Bearer {$accessToken}",
    //     ])->get('https://api.linkedin.com/v2/me');

    //     if ($profile2Response->failed()) {
    //         Log::error('Error: Failed to retrieve LinkedIn profile.');
    //         return redirect()->route('home')->withErrors('Error: Failed to retrieve LinkedIn2 profile.');
    //     }

    //     Log::info('Profile response: ' . $profileResponse->body());
    //     if ($profileResponse->failed()) {
    //         return response()->json(['error' => 'Failed to retrieve profile'], 500);
    //     }
    

    //     $profileData = $profileResponse->json();
    //     $profile2Data = $profile2Response->json();


    //     Log::info('LinkedIn Profile Data:', $profileData);
    //     Log::info($profileData);
    //     Log::info($profile2Data);

    //     User::updateOrCreate(
    //         ['email' => $profileData['email']], 
    //         [
    //             'name' => $profileData['name'],
    //             'given_name' => $profileData['given_name'] ?? null,
    //             'family_name' => $profileData['family_name'] ?? null,
    //             'locale' => $profileData['locale'] ?? [],
    //             'picture' => $profileData['picture'] ?? null,
    //             'email_verified' => $profileData['email_verified'] ?? false,
    //             'localizedHeadline' => $profile2Data['localizedHeadline'] ?? false,
    //         ]
    //     );
    
    //     return response()->json([
    //         'name' => $profileData['name'],
    //         'picture' => $profileData['picture'],
    //         'given_name'  => $profileData['given_name'],
    //         'family_name'  => $profileData['family_name'],
    //         'email'  => $profileData['email'],
    //         'locale' => $profileData['locale'],
    //         'localizedHeadline' => $profile2Data['localizedHeadline'] ?? false,

    //     ]);
    // }
public function getProfileData()
{
    try {
        // Vérifier l'authentification
        if (!Auth::check()) {
            Log::warning('User not authenticated in getProfileData');
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userId = Auth::id();
        Log::info("Fetching profile for authenticated User ID: " . $userId);

        $user = User::find($userId);
        
        if (!$user) {
            Log::error("User not found in database for ID: " . $userId);
            return response()->json(['error' => 'User not found'], 404);
        }

        Log::info("User data retrieved successfully for: " . $user->email);

        return response()->json([
            'user' => [
                'name' => $user->name,
                'profile_picture' => $user->picture,
                'email' => $user->email,
                'given_name' => $user->given_name,
                'family_name' => $user->family_name,
                'locale' => $user->locale,
                'headline' => $user->localizedHeadline,
            ]
        ]);

    } catch (\Exception $e) {
        Log::error("Error in getProfileData: " . $e->getMessage());
        Log::error($e->getTraceAsString());
        return response()->json(['error' => 'Internal server error'], 500);
    }
}
    
    
}
