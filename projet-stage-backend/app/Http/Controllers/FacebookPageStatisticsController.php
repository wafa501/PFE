<?php 

namespace App\Http\Controllers;
use App\Models\FacebookPage;

use Illuminate\Support\Facades\Http;  
use App\Models\FacebookMetric;
use App\Models\FacebookUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FacebookPageStatisticsController extends Controller 
{     
    public function fetchAllStats($pageId, $accessToken)     
    {         
        if (!$accessToken) {             
            return response()->json(['error' => 'Access token not found'], 404);         
        }         

        $url = "https://graph.facebook.com/v17.0/{$pageId}/insights?metric=page_impressions,page_fans,page_views_total";

        do {
            $StatsResponse = Http::withHeaders([             
                'Authorization' => "Bearer {$accessToken}",         
            ])->get($url);
            
            if ($StatsResponse->failed()) {             
                Log::error('Error: Failed to retrieve Facebook stats data.');             
                return response()->json(['error' => 'Failed to retrieve Facebook data.'], 500);         
            }         

            $data = $StatsResponse->json();         

            foreach ($data['data'] as $metric) {             
                foreach ($metric['values'] as $value) {
                    $endTime = \Carbon\Carbon::parse($value['end_time'])->format('Y-m-d H:i:s');
                    
                    FacebookMetric::updateOrCreate(
                        [
                            'fb_id' => $metric['id'], 
                        ],
                        [
                            'name' => $metric['name'],
                            'title' => $metric['title'],
                            'description' => $metric['description'],
                            'period' => $metric['period'],
                            'value' => $value['value'],
                            'end_time' => $endTime

                        ]
                    );
                }
            }
            
            $url = $data['paging']['next'] ?? null;
        } while ($url); 

        return response()->json(['message' => 'All statistics data updated successfully.']);
    }
    public function getstatsDetailFromDatabase()
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
        
        Log::info($pages);
    
        $pageIds = $pages->pluck('page_id')->map(function ($pageId) {
            return explode('/', $pageId)[0]; 
        });
    
        Log::info('IDs des pages à rechercher :', $pageIds->toArray());
    
        $metricsCollection = collect(); 
    
        foreach ($pageIds as $pageId) {
            $metrics = FacebookMetric::where('fb_id', 'LIKE', $pageId . '/%')->get();
    
            if ($metrics->isEmpty()) {
                Log::warning('Warning: aucune métrique trouvée pour la page ID ' . $pageId);
                continue; 
            }
    
            $metricsCollection = $metricsCollection->merge($metrics);
        }
    
        if ($metricsCollection->isEmpty()) {
            Log::error('Error: aucune métrique trouvée pour les pages de l’utilisateur ID ' . $user->id);
            return redirect()->route('home')->withErrors('Error: aucune métrique trouvée.');
        }
    
        return response()->json($metricsCollection);
    }
    
    
    
    
}
