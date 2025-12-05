<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\FacebookPage;
use Illuminate\Support\Facades\Auth;
use App\Models\FacebookUsers;

class FacebookAuthController extends Controller
{
    public function redirectToFacebook()
    {
        $client_id = config('services.facebook.client_id');
        $redirect_uri = urlencode(config('services.facebook.redirect'));
        $scope = 'email,pages_manage_engagement, manage_fundraisers,pages_manage_metadata,business_management,read_insights ,pages_show_list ,pages_read_user_content, pages_read_engagement , pages_manage_posts'; 

        //       $scope = 'email, read_insights ,pages_show_list, instagram_basic ,pages_read_user_content, pages_read_engagement , pages_manage_posts'; 

        $response_type = 'code';

        $url = "https://www.facebook.com/v12.0/dialog/oauth?response_type=$response_type&client_id=$client_id&redirect_uri=$redirect_uri&scope=$scope";

        Log::info('Redirecting to Facebook: ' . $url);

        return redirect($url);
    }

public function handleFacebookCallback(Request $request)
{
    try {
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('home')->withErrors('Missing authorization code.');
        }

        // Récupération Access Token
        $response = Http::asForm()->post('https://graph.facebook.com/v12.0/oauth/access_token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => config('services.facebook.redirect'),
            'code' => $code,
        ]);

        $accessToken = $response->json('access_token');

        // Récupération infos utilisateur
        $FacebookUsersResponse = Http::withToken($accessToken)->get('https://graph.facebook.com/me', [
            'fields' => 'id,name,email,first_name,last_name,picture.width(500).height(500)'
        ]);

        $FacebookUsersData = $FacebookUsersResponse->json();

        // Sauvegarde dans la base
        $FacebookUsers = FacebookUsers::updateOrCreate(
            ['facebook_id' => $FacebookUsersData['id']],
            [
                'name' => $FacebookUsersData['name'] ?? '',
                'email' => $FacebookUsersData['email'] ?? null,
                'given_name' => $FacebookUsersData['first_name'] ?? '',
                'family_name' => $FacebookUsersData['last_name'] ?? '',
                'access_token' => $accessToken ?? null,
            ]
        );

        Auth::login($FacebookUsers);
        Auth::guard('facebook')->login($FacebookUsers);

        // Récupération pages
        $PageResponse = Http::withToken($accessToken)->get('https://graph.facebook.com/me/accounts');
        $pageData = $PageResponse->json();
        if (!empty($pageData['data'])) {
            foreach ($pageData['data'] as $page) {
                FacebookPage::updateOrCreate(
                    ['facebook_user_id' => $FacebookUsers->id, 'page_id' => $page['id']],
                    ['page_access_token' => $page['access_token'] ?? null]
                );
            }
        }

        return redirect('http://localhost:3000/DashboardFacebook');

    } catch (\Exception $e) {
        Log::error('Facebook Callback Error: ' . $e->getMessage());
        return redirect()->route('home')->withErrors('Error: ' . $e->getMessage());
    }
}

public function getFacebookProfile()
{
    try {
        $user = Auth::guard('facebook')->user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $accessToken = $user->access_token;

        $response = Http::withToken($accessToken)->get('https://graph.facebook.com/me', [
            'fields' => 'id,name,email,first_name,last_name,picture.width(500).height(500)'
        ]);

        $data = $response->json();

        return response()->json([
            'user' => [
                'name' => $data['name'] ?? '',
                'given_name' => $data['first_name'] ?? '',
                'family_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? '',
                'profile_picture' => $data['picture']['data']['url'] ?? '',
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
