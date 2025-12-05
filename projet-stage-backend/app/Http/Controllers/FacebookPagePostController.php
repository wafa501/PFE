<?php 

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;  
use App\Models\FacebookPagePost;
use App\Models\FacebookPage;
use App\Models\FacebookUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class FacebookPagePostController extends Controller 
{     
    public function fetchAllPosts($pageId,$accessToken)     
{             
    if (!$accessToken) {             
        return response()->json(['error' => 'Access token not found'], 404);         
    }         
    
    $url = "https://graph.facebook.com/v17.0/{$pageId}/posts?fields=id,created_time,updated_time,privacy,place,status_type,attachments";
    
    do {
        $postsResponse = Http::withHeaders([             
            'Authorization' => "Bearer {$accessToken}",         
        ])->get($url);
        
        if ($postsResponse->failed()) {             
            Log::error('Error: Failed to retrieve Facebook posts data.');             
            return redirect()->route('home')->withErrors('Error: Failed to retrieve Facebook data.');         
        }         
        
        $data = $postsResponse->json();         
        
        foreach ($data['data'] as $post) {             
            $createdTime = $post['created_time'] ?? null;             
            $updatedTime = $post['updated_time'] ?? null;
            
            $formattedCreatedTime = $createdTime ? (new \DateTime($createdTime))->format('Y-m-d H:i:s') : null;             
            $formattedUpdatedTime = $updatedTime ? (new \DateTime($updatedTime))->format('Y-m-d H:i:s') : null;
            
            $attachments = $post['attachments']['data'] ?? [];             
            $pictures = [];             
            $videos = [];
            
            foreach ($attachments as $attachment) {                 
                if (isset($attachment['media']['image'])) {                     
                    $pictures[] = $this->cleanUrl($attachment['media']['image']['src']);                     
                    Log::info('Pictures:', $pictures);                 
                } elseif (isset($attachment['media']['video'])) {                     
                    $videos[] = $attachment['media']['video']['src'];                 
                }             
            }
            
            FacebookPagePost::updateOrCreate(                 
                ['fb_id' => $post['id']],                 
                [                     
                    'created_time' => $formattedCreatedTime,                     
                    'updated_time' => $formattedUpdatedTime,                     
                    'status_type' => $post['status_type'] ?? null,                     
                    'attachments' => isset($post['attachments']) ? json_encode($post['attachments'], JSON_UNESCAPED_SLASHES) : null,                     
                    'privacy' => isset($post['privacy']) ? json_encode($post['privacy'], JSON_UNESCAPED_SLASHES) : null,                     
                    'description' => $post['attachments']['data'][0]['description'] ?? null,                     
                    'pictures' => !empty($pictures) ? json_encode($pictures, JSON_UNESCAPED_SLASHES) : null,                     
                    'videos' => !empty($videos) ? json_encode($videos, JSON_UNESCAPED_SLASHES) : null,                 
                ]             
            );
        }
        
        $url = $data['paging']['next'] ?? null;
    } while ($url); 

    return response()->json(['message' => 'All posts data updated successfully.']);
}
public function fetchPagesPosts($pageId,$accessToken)     
{         
        
    
    $url = "https://graph.facebook.com/v17.0/{$pageId}/posts?fields=id,created_time,updated_time,privacy,place,status_type,attachments";
    
    do {
        $postsResponse = Http::withHeaders([             
            'Authorization' => "Bearer {$accessToken}",         
        ])->get($url);
        
        if ($postsResponse->failed()) {             
            Log::error('Error: Failed to retrieve Facebook posts data.');             
            return redirect()->route('home')->withErrors('Error: Failed to retrieve Facebook data.');         
        }         
        
        $data = $postsResponse->json();         
        
        foreach ($data['data'] as $post) {             
            $createdTime = $post['created_time'] ?? null;             
            $updatedTime = $post['updated_time'] ?? null;
            
            $formattedCreatedTime = $createdTime ? (new \DateTime($createdTime))->format('Y-m-d H:i:s') : null;             
            $formattedUpdatedTime = $updatedTime ? (new \DateTime($updatedTime))->format('Y-m-d H:i:s') : null;
            
            $attachments = $post['attachments']['data'] ?? [];             
            $pictures = [];             
            $videos = [];
            
            foreach ($attachments as $attachment) {                 
                if (isset($attachment['media']['image'])) {                     
                    $pictures[] = $this->cleanUrl($attachment['media']['image']['src']);                     
                    Log::info('Pictures:', $pictures);                 
                } elseif (isset($attachment['media']['video'])) {                     
                    $videos[] = $attachment['media']['video']['src'];                 
                }             
            }
            
            FacebookPagePost::updateOrCreate(                 
                ['fb_id' => $post['id']],                 
                [                     
                    'created_time' => $formattedCreatedTime,                     
                    'updated_time' => $formattedUpdatedTime,                     
                    'status_type' => $post['status_type'] ?? null,                     
                    'attachments' => isset($post['attachments']) ? json_encode($post['attachments'], JSON_UNESCAPED_SLASHES) : null,                     
                    'privacy' => isset($post['privacy']) ? json_encode($post['privacy'], JSON_UNESCAPED_SLASHES) : null,                     
                    'description' => $post['attachments']['data'][0]['description'] ?? null,                     
                    'pictures' => !empty($pictures) ? json_encode($pictures, JSON_UNESCAPED_SLASHES) : null,                     
                    'videos' => !empty($videos) ? json_encode($videos, JSON_UNESCAPED_SLASHES) : null,                 
                ]             
            );
        }
        
        $url = $data['paging']['next'] ?? null;
    } while ($url); 

    return response()->json(['message' => 'All posts data updated successfully.']);
}
   

    private function cleanUrl($url) 
    {     
        return str_replace('\/', '/', $url); 
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
            $userPosts = FacebookPagePost::where(function($query) use ($pageIds) {
            foreach ($pageIds as $pageId) {
                $query->orWhere('fb_id', 'LIKE', $pageId . '_%');
            }
        })->get();
            if ($userPosts->isEmpty()) {
            Log::error('Erreur : Aucun post trouvé pour les pages de l\'utilisateur avec ID ' . $user->id);
            return redirect()->route('home')->withErrors('Erreur : Aucun post trouvé.');
        }
            return response()->json($userPosts);
    }
    
    public function getUserIdByEmail($email)
    {
        $user = FacebookUsers::where('email', $email)->first();
        if ($user) {
            return response()->json(['facebook_id' => $user->facebook_id]);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
    public function getPageById($pageId)
    {
        try {
            $page = Page::where('user_id', $userId)->first();
            
            if (!$page) {
                return response()->json(['message' => 'Page not found.'], 404);
            }
    
            return response()->json($page);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while retrieving the page: ' . $e->getMessage()], 500);
        }
    }
    
    
    
    
}
