<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Statistics;
use Carbon\Carbon;
use App\Models\LinkedInToken;

class StatisticsController extends Controller
{
    public function showAllYears($organizationId,$accessToken)
    {
           $user = User::first(); 
    $accessToken = $user->linkedin_token;

        if (!$accessToken) {
            Log::error('Error: Missing access token.');
            return redirect()->route('home')->withErrors('Error: Missing access token.');
        }
        
        Log::info('Access token page: ' . $accessToken);

        $currentYear = Carbon::now()->year;
        $startYear = 2023; 

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $startOfYear = Carbon::create($year, 1, 1)->startOfYear()->timestamp * 1000;
            $endOfYear = Carbon::create($year, 12, 31)->endOfYear()->timestamp * 1000;
            
            Log::info("startyear". $startOfYear);
            Log::info("endyear". $endOfYear);

            $allStatistics = $this->fetchStatistics($accessToken, $organizationId, $startOfYear, $endOfYear);

            $monthlyStats = $this->formatStatisticsByMonth($allStatistics);

            Statistics::updateOrCreate(
                ['organization' => $organizationId, 'year' => $year],
                ['monthly_stats' => $monthlyStats]
            );

        }
    }

    public function showYear($organizationId, $year)
    {
        $tokenRecord = LinkedInToken::find(1); 
        $accessToken = $tokenRecord ? $tokenRecord->access_token : null;

        if (!$accessToken) {
            Log::error('Error: Missing access token.');
            return redirect()->route('home')->withErrors('Error: Missing access token.');
        }

        Log::info('Access token page: ' . $accessToken);

        $startOfYear = Carbon::create($year, 1, 1)->startOfYear()->timestamp * 1000;
        $endOfYear = Carbon::create($year, 12, 31)->endOfYear()->timestamp * 1000;

        $allStatistics = $this->fetchStatistics($accessToken, $organizationId, $startOfYear, $endOfYear);


        $monthlyStats = $this->formatStatisticsByMonth($allStatistics);

        Statistics::updateOrCreate(
            ['organization' => $organizationId, 'year' => $year],
            ['monthly_stats' => $monthlyStats]
        );
    }

    private function fetchStatistics($accessToken, $organizationId, $startDate, $endDate)
    {
        $allStatistics = [];
        $url = "https://api.linkedin.com/rest/organizationalEntityShareStatistics?q=organizationalEntity&organizationalEntity=urn%3Ali%3Aorganization%3A{$organizationId}&timeIntervals.timeGranularityType=MONTH&timeIntervals.timeRange.start={$startDate}&timeIntervals.timeRange.end={$endDate}";
        while ($url) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'LinkedIn-Version' => "202511",
            ])->get($url);

            if ($response->failed()) {
                Log::error('Error: Failed to retrieve LinkedIn stats data. Response: ' . $response->body());
                return $allStatistics;
            }

            $data = $response->json();
            $statistics = $data['elements'] ?? [];
            $allStatistics = array_merge($allStatistics, $statistics);
            Log::info('LinkedIn stats data retrieved successfully.', [
                'url' => $url,
                'data' => $data
            ]);

            $paging = $data['paging'] ?? [];
            Log::info('Paging Data', ['paging' => $paging]);

            break;
        }

        return $allStatistics;
    }

    private function formatStatisticsByMonth($statistics)
    {
        $monthlyStats = [];

        foreach ($statistics as $stat) {
            $date = $stat['timeRange']['start'] ?? null;
            $month = $date ? Carbon::createFromTimestampMs($date)->month : null;

            if ($month && !isset($monthlyStats[$month])) {
                $monthlyStats[$month] = [
                    'uniqueImpressionsCount' => 0,
                    'shareCount' => 0,
                    'shareMentionsCount' => 0,
                    'engagement' => 0,
                    'clickCount' => 0,
                    'likeCount' => 0,
                    'impressionCount' => 0,
                    'commentMentionsCount' => 0,
                    'commentCount' => 0,
                ];
            }

            if ($month && isset($monthlyStats[$month])) {
                $monthlyStats[$month]['uniqueImpressionsCount'] += data_get($stat, 'totalShareStatistics.uniqueImpressionsCount', 0);
                $monthlyStats[$month]['shareCount'] += data_get($stat, 'totalShareStatistics.shareCount', 0);
                $monthlyStats[$month]['shareMentionsCount'] += data_get($stat, 'totalShareStatistics.shareMentionsCount', 0);
                $monthlyStats[$month]['engagement'] += data_get($stat, 'totalShareStatistics.engagement', 0);
                $monthlyStats[$month]['clickCount'] += data_get($stat, 'totalShareStatistics.clickCount', 0);
                $monthlyStats[$month]['likeCount'] += data_get($stat, 'totalShareStatistics.likeCount', 0);
                $monthlyStats[$month]['impressionCount'] += data_get($stat, 'totalShareStatistics.impressionCount', 0);
                $monthlyStats[$month]['commentMentionsCount'] += data_get($stat, 'totalShareStatistics.commentMentionsCount', 0);
                $monthlyStats[$month]['commentCount'] += data_get($stat, 'totalShareStatistics.commentCount', 0);
            }
        }

        return $monthlyStats;
    }

    public function getMyStatsDataFromDatabase()
    {
        $userId = Auth::id();
        Log::info("userid1".$userId);
        $user = User::find($userId);
        $accessToken = $user->linkedin_token ?? null; 

        if (!$accessToken) {
            Log::error('Error: Missing access token.');
            return redirect()->route('home')->withErrors('Error: Missing access token.');
        }
        $stats = Statistics::all();

        if (!$stats) {
            Log::error('Error: stats not found.');
            return redirect()->route('home')->withErrors('Error: stats not found.');
        }

        Log::info('Stats Data:', $stats->toArray());

        return response()->json($stats);
        
    }
}
