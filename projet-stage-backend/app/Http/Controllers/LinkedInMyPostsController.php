<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\MyPosts;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Models\LinkedInToken;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use App\Models\Posts; 

class LinkedInMyPostsController extends Controller
{
    public function showDetails($accessToken)
    {
        $userId = Auth::id();
        Log::info("User ID: " . $userId);
    
        if (!$accessToken) {
            return redirect()->route('home')->withErrors('Error: Missing access token.');
        }
    
        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'LinkedIn-Version' => "202307",
        ])->get('https://api.linkedin.com/v2/organizationAcls?q=roleAssignee&role=ADMINISTRATOR&state=APPROVED');
    
        if ($response->failed()) {
            Log::error('Failed to retrieve admin pages: ' . $response->body());
            return response()->json(['error' => 'Failed to retrieve admin pages'], 500);
        }
    
        $organizations = $response->json('elements');
        Log::info("Number of organizations: " . count($organizations));
    
        $allPosts = [];
    
        foreach ($organizations as $org) {
            $organizationUrn = $org['organization'];
            $id = str_replace('urn:li:organization:', '', $organizationUrn);
    
            $posts = $this->fetchAllPosts($accessToken, $id);
            Log::info("Processing organization ID: " . $id);
            Log::info("Number of posts fetched for organization ID $id: " . count($posts));
    
            $allPosts = array_merge($allPosts, $posts);
    
            foreach ($posts as $post) {
                Log::info("Processing post ID: " . data_get($post, 'id')); 
                $this->processPost($post, $accessToken, $id);
            }
        }
    
        $currentLinkedInPostIds = array_map(fn($post) => data_get($post, 'id'), $allPosts); 
        $dbPostIds = MyPosts::pluck('idPost')->toArray();
    
        $postsToDelete = array_diff($dbPostIds, $currentLinkedInPostIds);
        MyPosts::whereIn('idPost', $postsToDelete)->delete();
        Log::info('Deleted posts: ' . json_encode($postsToDelete));
    
        return view('MyPosts_Page', [
            'allPosts' => $allPosts,
            'nextPage' => null,
            'previousPage' => null,
        ]);
    }
    
    
    private function processPost($post, $accessToken, $pageId)
    {
        $publishedAt = data_get($post, 'publishedAt');
        $lastModifiedAt = data_get($post, 'lastModifiedAt');
        $idPost = data_get($post, 'id');
        Log::info("Processing post for page ID: $pageId, Post ID: $idPost");
    
        Log::info("Post data: " . json_encode($post));
    
        $contentArray = data_get($post, 'content', []);
        $imageURL = null;
        $videoURL = null;
    
        $responseComments = $this->fetchComments($accessToken, $idPost);
        $responseLikes = $this->fetchLikes($accessToken, $idPost);
    
        $likesCount = $responseLikes['paging']['total'] ?? 0;
        $commentsCount = $responseComments['paging']['total'] ?? 0;
    
        $commentsList = [];
        if (!empty($responseComments['elements'])) {
            foreach ($responseComments['elements'] as $comment) {
                $commentsList[] = [
                    'comment' => data_get($comment, 'message.text', ''),
                    'author' => data_get($comment, 'actor', ''),
                ];
            }
        }
    
        $this->retrieveMediaUrls($contentArray, $accessToken, $imageURL, $videoURL);
    
        $lastModifiedAt = $this->convertTimestamp($lastModifiedAt);
        $publishedAt = $this->convertTimestamp($publishedAt);
       Log::info("IMG_URL: " . $imageURL);
       Log::info("VIDEO_URL: " . $videoURL);

        $postSaved = MyPosts::updateOrCreate(
            ['idPost' => $idPost],
            [
                'page_id' => $pageId,
                'published_at' => $publishedAt,
                'last_modified_at' => $lastModifiedAt,
                'lifecycle_state' => data_get($post, 'lifecycleState', 'Unknown'),
                'visibility' => data_get($post, 'visibility', 'Unknown'),
                'distribution' => json_encode(data_get($post, 'distribution', [])),
                'content' => json_encode($contentArray),
                'commentary' => data_get($post, 'commentary', ''),
                'altText' => data_get($post, 'content.media.altText', ''),
                'image_url' => $imageURL,
                'video_url' => $videoURL,
                'likes' => json_encode($responseLikes),
                'comments' => json_encode($responseComments),
                'numberLikes' => $likesCount,
                'numberComments' => $commentsCount,
                'CommentsList' => json_encode($commentsList),
            ]
        );
    
        if ($postSaved) {
            Log::info("Successfully saved post: " . $idPost);
        } else {
            Log::error("Failed to save post: " . $idPost);
        }
    }
    
    private function retrieveMediaUrls($contentArray, $accessToken, &$imageURL, &$videoURL)
    {
        if (isset($contentArray['media']['id'])) {
            $mediaId = $contentArray['media']['id'];
    
            if (strpos($mediaId, 'image') !== false) {
                $responseImg = Http::withHeaders(['Authorization' => "Bearer $accessToken"])
                    ->get("https://api.linkedin.com/v2/images/{$mediaId}");
    
                if ($responseImg->successful()) {
                    $imageURL = data_get($responseImg->json(), 'downloadUrl', null);
                } else {
                    Log::error('Failed to retrieve image. Status: ' . $responseImg->status());
                }
    
            } elseif (strpos($mediaId, 'video') !== false) {
                $responseVid = Http::withHeaders(['Authorization' => "Bearer $accessToken"])
                    ->get("https://api.linkedin.com/v2/videos/{$mediaId}");
    
                if ($responseVid->successful()) {
                    $videoURL = data_get($responseVid->json(), 'downloadUrl', null);
                } else {
                    Log::error('Failed to retrieve video. Status: ' . $responseVid->status());
                }
            }
        }
    
        if (isset($contentArray['multiImage']['images'])) {
            $images = $contentArray['multiImage']['images'];
            $imageURLs = [];
    
            foreach ($images as $image) {
                $mediaId = $image['id'];
                $responseImg = Http::withHeaders(['Authorization' => "Bearer $accessToken"])
                    ->get("https://api.linkedin.com/v2/images/{$mediaId}");
    
                if ($responseImg->successful()) {
                    $imageURLs[] = data_get($responseImg->json(), 'downloadUrl', null);
                } else {
                    Log::error('Failed to retrieve image. Status: ' . $responseImg->status());
                }
            }
    
            $imageURL = implode(',', $imageURLs);
        }
    }
    

/*
private function retrieveMediaUrls($contentArray, $accessToken, &$imageURL, &$videoURL)
{
    if (!isset($contentArray['media']['id'])) return;

    $mediaId = $contentArray['media']['id'];

    $assetResponse = Http::withHeaders([
        'Authorization' => "Bearer $accessToken",
    ])->get("https://api.linkedin.com/v2/assets/{$mediaId}?projection=(playableStreams,digitalmediaAsset)");

    if (!$assetResponse->successful()) {
        Log::error("Asset retrieval failed: " . $assetResponse->body());
        return;
    }

    $asset = $assetResponse->json();

    if (isset($asset['digitalmediaAsset']['storage']['media']['artifact'])) {
        $imageURL = $asset['digitalmediaAsset']['storage']['media']['artifact'];
        Log::info("Extracted image URL: " . $imageURL);
    }

    if (isset($asset['playableStreams'][0]['streamingLocation']['url'])) {
        $videoURL = $asset['playableStreams'][0]['streamingLocation']['url'];
        Log::info("Extracted video URL: " . $videoURL);
    }
}
*/
public function convertTimestamp($timestampMs)
{
    $timestampSec = $timestampMs / 1000;

    $dateTime = Carbon::createFromTimestamp($timestampSec);

    $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

    return $formattedDateTime; 
}
    private function fetchAllPosts($accessToken, $organizationId)
    {
        $allPosts = [];
        $count = 100; 
        $url = "https://api.linkedin.com/v2/posts?q=author&author=urn:li:organization:{$organizationId}&count={$count}";
    
        while ($url) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'LinkedIn-Version' => "202307",
            ])->get($url);
    
            if ($response->failed()) {
                Log::error('Error: Failed to retrieve LinkedIn posts data. Response: ' . $response->body());
                return $allPosts;
            }
    
            $data = $response->json();
            $posts = $data['elements'] ?? [];
            $allPosts = array_merge($allPosts, $posts);
    
            $paging = $data['paging'] ?? [];
            $url = $paging['next'] ?? null;  
    
            Log::info('Paging Data', ['paging' => $paging]);
    
            if (!$url) {
                break;
            }
        }
    
        return $allPosts;
    }
    private function fetchComments($accessToken, $postId)
{
    $url = "https://api.linkedin.com/v2/socialActions/{$postId}/comments";
    $response = Http::withHeaders([
        'Authorization' => "Bearer $accessToken",
    ])->get($url);

    if ($response->successful()) {
        return $response->json();
    } else {
        Log::error('Failed to retrieve comments. Status: ' . $response->status());
        return [];
    }
}

private function fetchLikes($accessToken, $postId)
{
    $url = "https://api.linkedin.com/v2/socialActions/{$postId}/likes";
    $response = Http::withHeaders([
        'Authorization' => "Bearer $accessToken",
    ])->get($url);

    if ($response->successful()) {
        return $response->json();
    } else {
        Log::error('Failed to retrieve likes. Status: ' . $response->status());
        return [];
    }
}
public function getPostsDataFromDatabase($id)
{
    try {
        Log::info("Received request for posts with ID: " . $id);

        if (!is_numeric($id)) {
            return response()->json([
                'error' => 'Invalid user ID format'
            ], 400);
        }

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $accessToken = $user->linkedin_token;

        if (!$accessToken) {
            Log::warning("No LinkedIn token for user: " . $id);
            // Continuer quand même pour récupérer les posts existants
        }

        Log::info($id);
        //$myposts = MyPosts::where('page_id', $id)->get();
        $newAuth = "urn:li:organization:".$id;
                Log::info($newAuth);

          $myposts = Posts::where('author', $newAuth)->get();
        if ($myposts->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No posts found for this user',
                'posts' => []
            ], 200);
        }

        Log::info("Successfully found " . $myposts->count() . " posts for user " . $id);
        
        return response()->json([
            'success' => true,
            'posts' => $myposts,
            'count' => $myposts->count()
        ]);

    } catch (\Exception $e) {
        Log::error('Error in getPostsDataFromDatabase: ' . $e->getMessage());
        return response()->json([
            'error' => 'Internal server error: ' . $e->getMessage()
        ], 500);
    }
}
    
    
}
