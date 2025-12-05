<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;  
use App\Models\FacebookReactionPost;
use App\Models\FacebookUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\FacebookPage;
use App\Models\FacebookMetric;
use Illuminate\Support\Facades\Auth;
class FacebookPageReactionController extends Controller 
{
    public function fetchAllReactions($pageId, $facebookId)
    {
        $accessToken = FacebookUsers::getPageAccessTokenByFacebookId($facebookId, $pageId);
        Log::info("Access token : ". $accessToken);

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
                $idPost = $post['id'];

                $urlLikes = "https://graph.facebook.com/v18.0/{$idPost}/reactions?summary=total_count";
                $LikesResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                ])->get($urlLikes);

                if ($LikesResponse->failed()) {
                    Log::error('Error: Failed to retrieve likes posts data.');
                    return redirect()->route('home')->withErrors('Error: Failed to retrieve Facebook data.');
                }

                $dataLikes = $LikesResponse->json();

                $reactionCounts = [
                    'LIKE' => 0,
                    'LOVE' => 0,
                    'WOW' => 0,
                    'HAHA' => 0,
                    'SAD' => 0,
                    'ANGRY' => 0,
                ];
                $totalReactions = 0;

                foreach ($dataLikes['data'] as $reaction) {
                    $reactionType = $reaction['type'];
                    if (isset($reactionCounts[$reactionType])) {
                        $reactionCounts[$reactionType]++;
                    }
                    $totalReactions++;
                }

                $urlComments = "https://graph.facebook.com/v18.0/{$idPost}/comments?summary=total_count";
                $CommentsResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                ])->get($urlComments);

                if ($CommentsResponse->failed()) {
                    Log::error('Error: Failed to retrieve comment posts data.');
                    return redirect()->route('home')->withErrors('Error: Failed to retrieve Facebook data.');
                }

                $dataComments = $CommentsResponse->json();

                $commentsMessages = [];
                foreach ($dataComments['data'] as $comment) {
                    $commentsMessages[] = $comment['message'];
                }
                $messageComments = implode("\n", $commentsMessages);

                FacebookReactionPost::updateOrCreate(
                    ['post_id' => $idPost], 
                    [
                        'like_count' => $reactionCounts['LIKE'],
                        'love_count' => $reactionCounts['LOVE'],
                        'wow_count' => $reactionCounts['WOW'],
                        'haha_count' => $reactionCounts['HAHA'],
                        'sad_count' => $reactionCounts['SAD'],
                        'angry_count' => $reactionCounts['ANGRY'],
                        'total_reactions' => $totalReactions,
                        'comments_count' => $dataComments['summary']['total_count'] ?? 0,
                        'message_comments' => $messageComments,
                    ]
                );

                Log::info("Saved post ID {$idPost} reactions and comments in the database.");
            }

            $url = $data['paging']['next'] ?? null;
        } while ($url);

        return response()->json(['message' => 'All reactions data updated and saved successfully.']);
    }
    public function fetchAllPagesReactions($pageId, $facebookId)
    {
        $accessToken = FacebookUsers::getAccessTokenByFacebookId($facebookId);    
    Log::info("Access token : ". $accessToken);         
    
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
                $idPost = $post['id'];

                $urlLikes = "https://graph.facebook.com/v18.0/{$idPost}/reactions?summary=total_count";
                $LikesResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                ])->get($urlLikes);

                if ($LikesResponse->failed()) {
                    Log::error('Error: Failed to retrieve likes posts data.');
                    return redirect()->route('home')->withErrors('Error: Failed to retrieve Facebook data.');
                }
                $urlSummary = "https://graph.facebook.com/v18.0/{$idPost}?fields=shares,reactions.summary(true),comments.summary(true)";
                $urlSummaryResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                ])->get($urlSummary);
                if ($urlSummaryResponse->failed()) {
                    Log::error('Error: Failed to retrieve summary posts data.');
                    return redirect()->route('home')->withErrors('Error: Failed to retrieve Facebook data.');
                }
                $summaryData = $urlSummaryResponse->json(); 
                //Log::info("Summary Data: ". json_encode($summaryData)); 
                $sharesCount1 = $summaryData['shares']['count'] ?? 0;
                //Log::info("Shares Count: ".$sharesCount1);
                $totalReactions1 = $summaryData['reactions']['summary']['total_count'] ?? 0;
                //Log::info("Total Reactions Count: ".$totalReactions1);
                $commentsCount1 = $summaryData['comments']['summary']['total_count'] ?? 0;
                //Log::info("Comments Count: ".$commentsCount1);
                
                $dataLikes = $LikesResponse->json();

                $reactionCounts = [
                    'LIKE' => 0,
                    'LOVE' => 0,
                    'WOW' => 0,
                    'HAHA' => 0,
                    'SAD' => 0,
                    'ANGRY' => 0,
                ];
                $totalReactions = 0;

                foreach ($dataLikes['data'] as $reaction) {
                    $reactionType = $reaction['type'];
                    if (isset($reactionCounts[$reactionType])) {
                        $reactionCounts[$reactionType]++;
                    }
                    $totalReactions++;
                }

                $urlComments = "https://graph.facebook.com/v18.0/{$idPost}/comments?summary=total_count";
                $CommentsResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                ])->get($urlComments);

                if ($CommentsResponse->failed()) {
                    Log::error('Error: Failed to retrieve comment posts data.');
                    return redirect()->route('home')->withErrors('Error: Failed to retrieve Facebook data.');
                }

                $dataComments = $CommentsResponse->json();

                $commentsMessages = [];
                foreach ($dataComments['data'] as $comment) {
                    $commentsMessages[] = $comment['message'];
                }
                $messageComments = implode("\n", $commentsMessages);

                FacebookReactionPost::updateOrCreate(
                    ['post_id' => $idPost], 
                    [
                        'like_count' => $reactionCounts['LIKE'],
                        'love_count' => $reactionCounts['LOVE'],
                        'wow_count' => $reactionCounts['WOW'],
                        'haha_count' => $reactionCounts['HAHA'],
                        'sad_count' => $reactionCounts['SAD'],
                        'angry_count' => $reactionCounts['ANGRY'],
                        'total_reactions' => $totalReactions1,
                        'comments_count' => $commentsCount1,
                        'message_comments' => $messageComments,
                    ]
                );

                //Log::info("Saved post ID {$idPost} reactions and comments in the database.");
            }

            $url = $data['paging']['next'] ?? null;
        } while ($url);

        return response()->json(['message' => 'All reactions data updated and saved successfully.']);
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
        Log::error('Error: aucune page trouvée pour l’utilisateur ID ' . $user->id);
        return redirect()->route('home')->withErrors('Error: aucune métrique trouvée.');
    }
    
    //Log::info($pages);
    $pageIds = $pages->pluck('page_id')->map(function ($pageId) {
        return explode('/', $pageId)[0]; 
    });

    //Log::info('IDs des pages à rechercher :', $pageIds->toArray());
    $reactionsCollection = collect(); 

    foreach ($pageIds as $pageId) {
        $reactions = FacebookReactionPost::where('post_id', 'LIKE', $pageId . '_%')->get();

        if ($reactions->isEmpty()) {
            Log::warning('Warning: aucune réaction trouvée pour la page ID ' . $pageId);
            continue; 
        }

        $reactionsCollection = $reactionsCollection->merge($reactions);
    }

    if ($reactionsCollection->isEmpty()) {
        Log::error('Error: aucune réaction trouvée pour les pages de l’utilisateur ID ' . $user->id);
        return redirect()->route('home')->withErrors('Error: aucune réaction trouvée.');
    }

    return response()->json($reactionsCollection);
}
public function fetchAllPageREACT()
{
    $pages = FacebookReactionPost::all();

    if ($pages->isNotEmpty()) {
        return response()->json($pages);
    }

    return response()->json(['message' => 'Aucune page trouvée'], 404);
}

}
