<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\FacebookOtherPageDetails;
use App\Models\FacebookUsers;

class FacebookOtherPagesDetails extends Controller
{
    /**
     * Retrieve Facebook page data and store it in the database.
     *
     * @param  string  $facebookId
     * @return \Illuminate\Http\Response
     */
    public function getStoreDetails($facebookId,$pageName,$pageId)
    {
        Log::info("call");
        $accessToken = FacebookUsers::getAccessTokenByFacebookId($facebookId);

        if (!$accessToken) {
            return response()->json(['error' => 'Access token not found'], 404);
        }

        $url = "https://graph.facebook.com/v12.0/search?type=page&q={$pageName}&fields=id,name,location,link,about,category,picture,fan_count,website&access_token={$accessToken}";

        $response = file_get_contents($url);

        if ($response === FALSE) {
            Log::error("Failed to fetch data from Facebook API for query 'ooredoo'");
            return response()->json(['error' => 'Failed to fetch data from Facebook API'], 500);
        }

        $data = json_decode($response, true);

        if (isset($data['data']) && !empty($data['data'])) {
            $pageToSave = collect($data['data'])->firstWhere('id', "$pageId");

            if ($pageToSave) {
                $location = isset($pageToSave['location']) ? json_encode($pageToSave['location']) : null; 
                $pictureUrl = isset($pageToSave['picture']['data']['url']) ? $pageToSave['picture']['data']['url'] : null; // Get picture URL

                FacebookOtherPageDetails::updateOrCreate(
                    ['id' => $pageToSave['id']], 
                    [
                        'name' => $pageToSave['name'],
                        'location' => $location, 
                        'link' => $pageToSave['link'] ?? null,
                        'about' => $pageToSave['about'] ?? null,
                        'category' => $pageToSave['category'] ?? null,
                        'picture' => $pictureUrl, 
                        'fan_count' => $pageToSave['fan_count'] ?? 0,
                        'website' => $pageToSave['website'] ?? null,
                    ]
                );

                return response()->json(['success' => 'Page details saved successfully'], 200);
            } else {
                return response()->json(['error' => 'Page not found'], 404);
            }
        } else {
            return response()->json(['error' => 'No data returned from Facebook API'], 404);
        }
    }
    public function getpagesFromDatabase(){
        $pages = FacebookOtherPageDetails::all(); 

        return response()->json($pages);
    }

}
