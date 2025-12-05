<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\OtherOrganization;
use App\Models\Posts;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Models\LinkedInToken;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use Illuminate\Support\Facades\Artisan;

class LinkedInOtherPageController extends Controller
{
    public function showDetails($vanityName, $id, $accessToken)
    {
        $userId = Auth::id();
        Log::info("userid1: " . $userId);

        if (!$accessToken) {
            Log::error('Error: Missing access token.');
            return redirect()->route('home')->withErrors('Error: Missing access token.');
        }

        Log::info('Access token page: ' . $accessToken);

        // Récupérer les informations de la page
        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'LinkedIn-Version' => '202411',
        ])->get("https://api.linkedin.com/v2/organizations?q=vanityName&vanityName={$vanityName}");

        if ($response->failed()) {
            Log::error('Error: Failed to retrieve LinkedIn page. Status: ' . $response->status());
            return redirect()->route('home')->withErrors('LinkedIn page not found.');
        }

        $responseData = $response->json();
        $pageData = $responseData['elements'][0] ?? [];

        if (!isset($pageData['id'])) {
            Log::error('Error: Invalid LinkedIn page data format.');
            return redirect()->route('home')->withErrors('Invalid LinkedIn page data format.');
        }

        // Récupérer les followers
        $orgId = $pageData['id'];
        $orgUrn = "urn:li:organization:{$orgId}";

        $responseFollowers = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'LinkedIn-Version' => "202307",
        ])->get("https://api.linkedin.com/rest/networkSizes/urn:li:organization:{$id}?edgeType=COMPANY_FOLLOWED_BY_MEMBER");
    
        if ($responseFollowers->failed()) {
            Log::error("Failed to retrieve followers. Status: " . $responseFollowers->status() . " | Body: " . $responseFollowers->body());
            $pageFollowers = ['firstDegreeSize' => 0];
        } else {
            $pageFollowers = $responseFollowers->json();
        }

        // Récupérer tous les posts avec la nouvelle méthode corrigée
        $allPosts = $this->fetchAllPostsCorrige($accessToken, $orgId);

        // Traitement du logo
        $pageData['logoUrl'] = null;
        if (isset($pageData['logoV2']['original'])) {
            $assetId = substr($pageData['logoV2']['original'], strrpos($pageData['logoV2']['original'], ':') + 1);
            $pageData['logoUrl'] = "https://media.licdn.com/dms/image/$assetId/profile-displayphoto-shrink_100_100";
        }

        Log::info('LinkedIn Page Data:', $pageData);
        Log::info('Nombre total de posts récupérés: ' . count($allPosts));

        // Traitement des posts avec les nouvelles statistiques
        $processedPosts = [];
        $processedPostIds = [];
        foreach ($allPosts as $post) {
            $ugcPost = data_get($post, 'id');
            $orgUrn = "urn:li:organization:" . $id;
              if (in_array($ugcPost, $processedPostIds)) {
        Log::info("Post déjà traité, ignoré: " . $ugcPost);
        continue;
    }
    
            if ($ugcPost && strpos($ugcPost, "ugcPost") !== false) {
                $stats = $this->fetchPostStats($accessToken, $ugcPost, $orgUrn);
                
                $publishedAt = $this->convertTimestampSafe(data_get($post, 'publishedAt'));
                $lastModifiedAt = $this->convertTimestampSafe(data_get($post, 'lastModifiedAt'));

                $contentArray = data_get($post, 'content', []);
                [$imageURL, $videoURL] = $this->extractMediaUrls($accessToken, $contentArray);

                // Sauvegarde organisation
                OtherOrganization::updateOrCreate(
                    ['linkedin_id' => $orgId],
                    [
                        'vanity_name' => $pageData['vanityName'] ?? null,
                        'followers' => $pageFollowers['firstDegreeSize'] ?? null,
                        'localized_name' => $pageData['localizedName'] ?? null,
                        'name' => $pageData['name'] ?? null,
                        'primary_organization_type' => $pageData['primaryOrganizationType'] ?? null,
                        'locations' => json_encode($pageData['locations'] ?? []),
                        'linkedin_id' => $orgId,
                        'localized_website' => $pageData['localizedWebsite'] ?? null, 
                        'logo_v2' => json_encode($pageData['logoV2'] ?? null),
                        'paging' => json_encode($pageData['paging'] ?? null),
                    ]
                );

                // Vérification doublons post
                $author = data_get($post, 'author', 'Unknown'); 
                $postExists = Posts::where('author', $author)
                    ->where('published_at', $publishedAt)
                    ->where('content', json_encode(Arr::sortRecursive($contentArray)))
                    ->exists();

                if (!$postExists) {
                    Posts::updateOrCreate(
                        ['idPost' => $ugcPost],
                        [
                            'author' => $author,
                            'published_at' => $publishedAt,
                            'last_modified_at' => $lastModifiedAt,
                            'lifecycle_state' => data_get($post, 'lifecycleState', 'Unknown'),
                            'visibility' => data_get($post, 'visibility', 'Unknown'),
                            'distribution' => json_encode(data_get($post, 'distribution', [])),
                            'content' => json_encode($contentArray),
                            'likes_count' => $stats['likeCount'] ?? 0,
                            'Comments_count' => $stats['commentCount'] ?? 0,
                            'uniqueImpressionsCount' => $stats['uniqueImpressionsCount'] ?? 0,
                            'commentary' => data_get($post, 'commentary', ''),
                            'altText' => data_get($post, 'content.media.altText', ''),
                            'image_url' => $imageURL,
                            'video_url' => $videoURL,
                        ]
                    );
                }
        $processedPostIds[] = $ugcPost; // Marquer comme traité

                $processedPosts[] = [
                    'post' => $post,
                    'stats' => $stats
                ];
            }
        }

        return view('plusdetailOrganisation', [
            'pageData' => $pageData,
            'pageFollowers' => $pageFollowers,
            'allPosts' => $processedPosts,
            'totalPosts' => count($processedPosts),
        ]);
    }
private function fetchAllPostsCorrige($accessToken, $organizationId)
{
    $allPosts = [];
    $start = 0;
    $count = 100;
    $hasMore = true;
    $iterations = 0;
    $maxIterations = 20; // Sécurité contre les boucles infinies
    
    $baseUrl = "https://api.linkedin.com/v2/posts";
    
    while ($hasMore && $iterations < $maxIterations) {
        $params = [
            'q' => 'author',
            'author' => "urn:li:organization:{$organizationId}",
            'count' => $count,
            'start' => $start // AJOUT CRITIQUE
        ];
        
        $queryString = http_build_query($params);
        $url = "{$baseUrl}?{$queryString}";
        
        Log::info("Fetching posts from: " . $url);
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'LinkedIn-Version' => '202409',
        ])->get($url);

        if ($response->failed()) {
            Log::error('Error fetching posts. Status: ' . $response->status() . ' Response: ' . $response->body());
            break;
        }

        $data = $response->json();
        $posts = $data['elements'] ?? [];
        
        Log::info('Page ' . ($iterations + 1) . ' - Posts récupérés: ' . count($posts));
        
        // Vérifier si on a des nouveaux posts
        if (empty($posts)) {
            Log::info('Aucun nouveau post récupéré - fin de la pagination');
            break;
        }
        
        $allPosts = array_merge($allPosts, $posts);
        $iterations++;

        // Gestion CORRIGÉE de la pagination
        $paging = $data['paging'] ?? [];
        
        // Méthode 1: Vérifier le total et calculer le prochain start
        if (isset($paging['total'])) {
            $totalPosts = $paging['total'];
            $nextStart = $start + $count;
            
            if ($nextStart >= $totalPosts) {
                $hasMore = false;
                Log::info("Pagination terminée - total: {$totalPosts}, prochain start: {$nextStart}");
            } else {
                $start = $nextStart;
                Log::info("Prochaine page - start: {$start}");
            }
        }
        // Méthode 2: Vérifier les liens de pagination
        elseif (isset($paging['links'])) {
            $hasNextPage = false;
            foreach ($paging['links'] as $link) {
                if ($link['rel'] === 'next') {
                    $hasNextPage = true;
                    // Extraire le paramètre start de l'URL next
                    if (preg_match('/start=(\d+)/', $link['href'], $matches)) {
                        $start = (int)$matches[1];
                    } else {
                        $start += $count;
                    }
                    break;
                }
            }
            $hasMore = $hasNextPage;
        }
        // Méthode 3: Fallback simple
        else {
            // Si moins de posts que demandé, c'est probablement la dernière page
            if (count($posts) < $count) {
                $hasMore = false;
                Log::info('Dernière page détectée (moins de posts que demandé)');
            } else {
                $start += $count;
                Log::info("Fallback - prochain start: {$start}");
            }
        }

        // Pause pour respecter les rate limits
        usleep(300000); // 0.3 seconde
        
        // Log intermédiaire
        Log::info("Progression: " . count($allPosts) . " posts récupérés après " . $iterations . " itérations");
    }

    Log::info('Total posts récupérés: ' . count($allPosts));
    
    // Éliminer les doublons basés sur l'ID du post
    $uniquePosts = [];
    $seenIds = [];
    
    foreach ($allPosts as $post) {
        $postId = data_get($post, 'id');
        if ($postId && !in_array($postId, $seenIds)) {
            $seenIds[] = $postId;
            $uniquePosts[] = $post;
        }
    }
    
    Log::info('Posts uniques après déduplication: ' . count($uniquePosts));
    
    return $uniquePosts;
}

    /**
     * MÉTHODE ALTERNATIVE si la première ne fonctionne pas
     */
    private function fetchAllPostsAlternative($accessToken, $organizationId)
    {
        $allPosts = [];
        $url = "https://api.linkedin.com/rest/posts?author=urn:li:organization:{$organizationId}&count=100";
        
        while ($url) {
            Log::info("Fetching: " . $url);
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'LinkedIn-Version' => '202409',
            ])->get($url);

            if ($response->failed()) {
                Log::error('Error: ' . $response->status() . ' - ' . $response->body());
                break;
            }

            $data = $response->json();
            $posts = $data['elements'] ?? [];
            $allPosts = array_merge($allPosts, $posts);

            // Gestion de la pagination LinkedIn REST API
            $paging = $data['paging'] ?? [];
            $nextUrl = null;
            
            if (isset($paging['links'])) {
                foreach ($paging['links'] as $link) {
                    if ($link['rel'] === 'next') {
                        $nextUrl = $link['href'];
                        break;
                    }
                }
            }
            
            $url = $nextUrl;
            
            // Pause pour rate limiting
            usleep(500000);
            
            // Limite de sécurité
            if (count($allPosts) >= 1000) break;
        }

        return $allPosts;
    }



    /**
     * CORRECTED METHOD: Fetch post statistics using the right endpoint
     */
   private function fetchPostStats($accessToken, $ugcPost, $organizationUrn)
{
  $ugcPostParam = urlencode($ugcPost);
  Log::info($ugcPostParam);
  Log::info($organizationUrn);
$url = "https://api.linkedin.com/rest/organizationalEntityShareStatistics?q=organizationalEntity&organizationalEntity={$organizationUrn}&ugcPosts={$ugcPostParam}";

$response = Http::withHeaders([
    'Authorization' => "Bearer $accessToken",
    'LinkedIn-Version' => '202509',
])->get($url);


    if ($response->successful()) {
        $data = $response->json();
        Log::info('fetchPostStats Response:', $data);

        $stats = $data['elements'][0]['totalShareStatistics'] ?? [];

        return [
            'likeCount' => $stats['likeCount'] ?? 0,
            'commentCount' => $stats['commentCount'] ?? 0,
            'uniqueImpressionsCount' => $stats['uniqueImpressionsCount'] ?? 0,
            'impressionCount' => $stats['impressionCount'] ?? 0,
            'clickCount' => $stats['clickCount'] ?? 0,
            'shareCount' => $stats['shareCount'] ?? 0,
            'engagement' => $stats['engagement'] ?? 0,
        ];
    } else {
        Log::error("Failed to retrieve post stats. Status: " . $response->status() . " | Body: " . $response->body());

        return [
            'likeCount' => 0,
            'commentCount' => 0,
            'uniqueImpressionsCount' => 0,
            'impressionCount' => 0,
            'clickCount' => 0,
            'shareCount' => 0,
            'engagement' => 0,
        ];
    }
}

    /**
     * ALTERNATIVE METHOD: Use organizationalEntityShareStatistics correctly
     */
    private function fetchPostStatsAlternative($accessToken, $orgId, $ugcPost)
    {
        $url = "https://api.linkedin.com/rest/organizationalEntityShareStatistics?q=organizationalEntity&organizationalEntity=urn:li:organization:{$orgId}";
        Log::info("fetchPostStatsAlternative URL: $url");

        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'LinkedIn-Version' => '202509',
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();
            // You'll need to filter for your specific post from the returned list
            foreach ($data['elements'] ?? [] as $element) {
                if ($element['share'] === $ugcPost) {
                    return $element['totalShareStatistics'] ?? [];
                }
            }
        }
        
        Log::error("Failed to retrieve alternative post stats. Status: " . $response->status());
        return [];
    }

    public function convertTimestamp($timestampMs)
    {
        $timestampSec = $timestampMs / 1000;
        $dateTime = Carbon::createFromTimestamp($timestampSec);
        return $dateTime->format('Y-m-d H:i:s');
    }

    private function fetchAllPosts($accessToken, $organizationId)
    {
        $allPosts = [];
        $count = 100; 
        $url = "https://api.linkedin.com/v2/posts?q=author&author=urn:li:organization:{$organizationId}&count={$count}";

        while ($url) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'LinkedIn-Version' => '202411', // Consistent version
            ])->get($url);

            if ($response->failed()) {
                Log::error('Error: Failed to retrieve LinkedIn posts data. Response: ' . $response->body());
                break;
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


    private function convertTimestampSafe($timestampMs)
    {
        if (!$timestampMs) return null;

        try {
            return Carbon::createFromTimestamp($timestampMs / 1000)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractMediaUrls($accessToken, $contentArray)
    {
        $imageURL = null;
        $videoURL = null;

        if (isset($contentArray['media']['id'])) {
            $mediaId = $contentArray['media']['id'];
            if (strpos($mediaId, 'image') !== false) {
                $imageURL = $this->fetchMediaUrl($accessToken, $mediaId, 'images');
            } elseif (strpos($mediaId, 'video') !== false) {
                $videoURL = $this->fetchMediaUrl($accessToken, $mediaId, 'videos');
            }
        }

        if (isset($contentArray['multiImage']['images'])) {
            $images = $contentArray['multiImage']['images'];
            $imageURLArray = [];
            foreach ($images as $image) {
                $imageURLArray[] = $this->fetchMediaUrl($accessToken, $image['id'], 'images');
            }
            $imageURL = implode(',', $imageURLArray);
        }

        return [$imageURL, $videoURL];
    }

    private function fetchMediaUrl($accessToken, $mediaId, $type)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'LinkedIn-Version' => '202411',
        ])->get("https://api.linkedin.com/v2/{$type}/{$mediaId}");

        if ($response->successful()) {
            return data_get($response->json(), 'downloadUrl', null);
        }
        Log::error("Failed to retrieve $type. Status: " . $response->status());
        return null;
    }
     public function getPostsDataFromDatabase()
{
    $userId = Auth::id();
    Log::info("userid1".$userId);
    $user = User::find($userId);
    $accessToken = $user->linkedin_token ?? null; 


    if (!$accessToken) {
        return redirect()->route('home')->withErrors('Error: Missing access token.');
    }

    $posts = Posts::all();

    if (!$posts) {
        Log::error('Error: posts not found.');
        return redirect()->route('home')->withErrors('Error: posts not found.');
    }

    Log::info(' posts Data:', $posts->toArray());

    return response()->json($posts);
}

public function getOrganizationNameByLinkedinId($linkedin_id)
{
    $userId = Auth::id();
    Log::info("userid1".$userId);
    $user = User::find($userId);
    $accessToken = $user->linkedin_token ?? null; 


    if (!$accessToken) {
        return redirect()->route('home')->withErrors('Error: Missing access token.');
    }
    
    $organization = OtherOrganization::where('linkedin_id', $linkedin_id)->first(); 

    if ($organization) {
        return response()->json(['vanity_name' => $organization->vanity_name]);
    }

    return response()->json(['error' => 'Organization not found'], 404);
}

public function listOrganizations()
{
    $organizations = OtherOrganization::all();
    return response()->json($organizations);
}


public function addOrganization(Request $request)
{
    $userId = Auth::id();
    Log::info("userid ".$userId);

    $user = User::find($userId);

    if (!$user) {
        return response()->json([
            'error' => "User avec ID {$userId} introuvable."
        ], 404);
    }

    $accessToken = $user->linkedin_token;

    Log::info("en fct add");

    $request->validate([
        'vanity_name' => 'required|string|unique:other_organizations,vanity_name',
        'name' => 'nullable|string',
    ]);

    try {
        Log::info("Fetching LinkedIn ID for vanityName: " . $request->vanity_name);
        
        $linkedinResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->get("https://api.linkedin.com/v2/organizations", [
            'q' => 'vanityName',
            'vanityName' => $request->vanity_name
        ]);

        if (!$linkedinResponse->successful()) {
            Log::error('LinkedIn API error: ' . $linkedinResponse->body());
            return response()->json([
                'error' => 'Erreur lors de la récupération des données LinkedIn: ' . $linkedinResponse->status()
            ], 400);
        }

        $linkedinData = $linkedinResponse->json();
        Log::info('LinkedIn API response:', $linkedinData);

        if (empty($linkedinData['elements']) || count($linkedinData['elements']) === 0) {
            return response()->json([
                'error' => 'Aucune organisation trouvée avec ce vanityName: ' . $request->vanity_name
            ], 404);
        }

        $organizationData = $linkedinData['elements'][0];
        $linkedinId = $organizationData['id'];
        
        Log::info("LinkedIn ID trouvé: " . $linkedinId . " pour vanityName: " . $request->vanity_name);

        $existingOrg = OtherOrganization::where('linkedin_id', $linkedinId)->first();
        if ($existingOrg) {
            return response()->json([
                'error' => 'Cette organisation existe déjà dans la base de données avec le vanityName: ' . $existingOrg->vanity_name
            ], 422);
        }

        $organization = OtherOrganization::create([
            'vanity_name' => $request->vanity_name,
            'linkedin_id' => $linkedinId,
            'name' => $request->name ?? $organizationData['localizedName'] ?? $organizationData['name']['localized']['en_US'] ?? null,
            'followers' => $request->followers ?? 0,
            'localized_website' => $organizationData['localizedWebsite'] ?? null,
        ]);

        Log::info("Organization créée avec ID: " . $organization->id);

        dispatch(function () use ($organization, $accessToken) {
    Log::info("Début du background job pour fetch posts de: " . $organization->vanity_name);
    
    try {
        set_time_limit(300);
        
        $controller = new LinkedInOtherPageController();
        $controller->showDetails(
            $organization->vanity_name,
            $organization->linkedin_id,
            $accessToken
        );
        Log::info("Background job terminé avec succès pour: " . $organization->vanity_name);
    } catch (\Exception $e) {
        Log::error("Erreur dans le background job pour " . $organization->vanity_name . ": " . $e->getMessage());
    }
})->afterResponse();

        return response()->json([
            'message' => 'Organization added successfully. Posts are being fetched in background.',
            'organization' => $organization,
            'linkedin_data' => [ 
                'id' => $linkedinId,
                'name' => $organizationData['localizedName'] ?? $organizationData['name']['localized']['en_US'] ?? null,
                'website' => $organizationData['localizedWebsite'] ?? null
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Erreur générale dans addOrganization: ' . $e->getMessage());
        return response()->json([
            'error' => 'Erreur interne du serveur: ' . $e->getMessage()
        ], 500);
    }
}
public function deleteOrganization($id)
{
    $organization = OtherOrganization::find($id);

    if (!$organization) {
        return response()->json(['error' => 'Organization not found'], 404);
    }

    // Supprimer les posts liés
    $linkedinUrn = 'urn:li:organization:' . $organization->linkedin_id;
    \App\Models\Posts::where('author', $linkedinUrn)->delete();

    // Supprimer l'organisation
    $organization->delete();

    return response()->json(['message' => 'Organization and its posts deleted successfully']);
}
public function getOtherDataFromDatabase($pageId)
{
    try {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $accessToken = $user->linkedin_token;
        Log::info($accessToken);
        if (!$accessToken) {
            return response()->json(['error' => 'Missing access token'], 400);
        }

        $organization = OtherOrganization::where('id', $pageId)->first();
        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        return response()->json($organization);

    } catch (\Exception $e) {
        Log::error('Error fetching organization: '.$e->getMessage());
        return response()->json(['error' => 'Server error'], 500);
    }
}

}