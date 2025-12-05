<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\FacebookOtherPageDetails;

use Illuminate\Http\Request;
use App\Models\FacebookPage;
use App\Models\FacebookUsers;
use App\Models\FacebookPageDetail;
use Illuminate\Support\Facades\Log;

class FacebookPageDetailsController extends Controller
{
    /**
     * Retrieve Facebook page data and store it in the database.
     *
     * @param  string  $pageId
     * @param  string  $accessToken
     * @return \Illuminate\Http\Response
     */
    public function fetchAndStorePageData($pageId, $accessToken)
    {
        if (!$accessToken) {
            return response()->json(['error' => 'Access token not found'], 404);
        }

        $url = "https://graph.facebook.com/v17.0/{$pageId}?fields=id,name,fan_count,about,category,website,location,hours,phone,price_range,mission,products&access_token={$accessToken}";

        $response = file_get_contents($url);

        if ($response === FALSE) {
            Log::error("Failed to fetch data from Facebook API for page ID {$pageId}");
            return response()->json(['error' => 'Failed to fetch data from Facebook API'], 500);
        }

        $data = json_decode($response, true);

        $pageDetail = FacebookPageDetail::updateOrCreate(
            ['fb_id' => $data['id']], 
            [
                'name' => $data['name'] ?? null,
                'fan_count' => $data['fan_count'] ?? null,
                'about' => $data['about'] ?? null,
                'category' => $data['category'] ?? null,
                'website' => $data['website'] ?? null,
                'phone' => $data['phone'] ?? null,
                'price_range' => $data['price_range'] ?? null,
                'mission' => $data['mission'] ?? null,
                'products' => $data['products'] ?? null,
                'hours' => $data['hours'] ?? null,
                'location' => $data['location'] ?? null
            ]
        );

     
    }
    public function getpageDetailFromDatabase()
    {
        if (!Auth::guard('facebook')->check()) {
            Log::error('Erreur : Utilisateur non authentifié.');
            return redirect()->route('home')->withErrors('Erreur : Vous devez être connecté.');
        }
        
        $user = Auth::guard('facebook')->user();
        $pages = FacebookPage::where('facebook_user_id', $user->id)->get();
    
        if ($pages->isEmpty()) {
            Log::error('Erreur : Aucune page trouvée pour l\'utilisateur avec ID ' . $user->id);
            return redirect()->route('home')->withErrors('Erreur : Aucune page trouvée.');
        }
    
        $pageIds = $pages->pluck('page_id');
        
        $pageDetails = FacebookPageDetail::whereIn('fb_id', $pageIds)->get();
    
        if ($pageDetails->isEmpty()) {
            Log::error('Erreur : Aucun détail de page trouvé pour les pages de l\'utilisateur avec ID ' . $user->id);
            return redirect()->route('home')->withErrors('Erreur : Aucun détail de page trouvé.');
        }
        
        Log::info('Détails de page renvoyés : ', $pageDetails->toArray());
        return response()->json($pageDetails);
        
    
    }
    public function getOtherPageName($id)
    {
        $page = FacebookOtherPageDetails::find($id);

        if ($page) {
            return response()->json(['name' => $page->name], 200);
        }

        return response()->json(['message' => 'Page non trouvée'], 404);
    }


public function index($facebookId)
{
    $user = FacebookUsers::where('facebook_id', $facebookId)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $pages = FacebookPage::with('posts') 
        ->where('facebook_user_id', $user->id)
        ->get();

    return response()->json($pages);
}


public function getPageNameById($idPage){
    try {
        $page = FacebookPageDetail::where('fb_id', $idPage)->first(); 
        
        if (!$page) {
            return response()->json(['message' => 'Page not found.'], 404);
        }

        return response()->json($page);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred while retrieving the page: ' . $e->getMessage()], 500);
    }
}
}
