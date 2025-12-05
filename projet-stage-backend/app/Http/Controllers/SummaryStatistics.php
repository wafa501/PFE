<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Statistics;
use Carbon\Carbon;
use App\Models\LinkedInToken;

class SummaryStatistics extends Controller
{
    public function showDetails($organizationId)
    {
        $tokenRecord = LinkedInToken::find(1); 
        $accessToken = $tokenRecord ? $tokenRecord->access_token : null;
    
        if (!$accessToken) {
            Log::error('Error: Missing access token.');
            return redirect()->route('home')->withErrors('Error: Missing access token.');
        }
    
        Log::info('Access token page: ' . $accessToken);
    
        $SumarryStats2 = $this->fetchStatistics2($accessToken, $organizationId);


        foreach ($SumarryStats2 as $stat) {
            $organization = data_get($stat, 'organizationalEntity');
            Statistics::updateOrCreate(
                ['organization' => $organization],
                [
                    'uniqueImpressionsCount' => data_get($stat, 'totalShareStatistics.uniqueImpressionsCount', 0),
                    'shareCount' => data_get($stat, 'totalShareStatistics.shareCount', 0),
                    'shareMentionsCount' => data_get($stat, 'totalShareStatistics.shareMentionsCount', 0),
                    'engagement' => data_get($stat, 'totalShareStatistics.engagement', 0),
                    'clickCount' => data_get($stat, 'totalShareStatistics.clickCount', 0),
                    'likeCount' => data_get($stat, 'totalShareStatistics.likeCount', 0),
                    'impressionCount' => data_get($stat, 'totalShareStatistics.impressionCount', 0),
                    'commentMentionsCount' => data_get($stat, 'totalShareStatistics.commentMentionsCount', 0),
                    'commentCount' => data_get($stat, 'totalShareStatistics.commentCount', 0),
                ]
            );
        }
        
    }

    private function fetchStatistics2($accessToken, $organizationId)
    {
        $SumarryStats = [];
        $url = "https://api.linkedin.com/rest/organizationalEntityShareStatistics?q=organizationalEntity&organizationalEntity=urn%3Ali%3Aorganization%3A{$organizationId}";
    
        while ($url) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'LinkedIn-Version' => "202402",
            ])->get($url);
    
            if ($response->failed()) {
                Log::error('Error: Failed to retrieve LinkedIn stats data. Response: ' . $response->body());
                return $SumarryStats;
            }
    
            $data = $response->json();
            $statistics = $data['elements'] ?? [];
            $SumarryStats = array_merge($SumarryStats, $statistics);
    
            $paging = $data['paging'] ?? [];    
            break;
        }
    
        return $SumarryStats;
    }
    public function getMyStatsDataFromDatabase()
    {
        $stats = Statistics::all();
    
        if (!$stats) {
            Log::error('Error: stats not found.');
            return redirect()->route('home')->withErrors('Error: stats not found.');
        }
    
        Log::info(' stats Data:', $stats->toArray());
    
        return response()->json($stats);
    }
}
