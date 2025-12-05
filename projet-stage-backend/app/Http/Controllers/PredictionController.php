<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Phpml\Regression\LeastSquares;
use App\Models\Statistics;

class PredictionController extends Controller
{
    public function predictStats($organizationId)
    {

        $statsData = Statistics::where('organization', $organizationId)->get();
        if ($statsData->isEmpty()) {
            return response()->json(['error' => 'Aucune donnée trouvée.'], 400);
        }

        $predictions = [];

        $metrics = [
            'uniqueImpressionsCount',
            'shareCount',
            'shareMentionsCount',
            'engagement',
            'clickCount',
            'likeCount',
            'impressionCount',
            'commentMentionsCount',
            'commentCount',
        ];

        foreach ($statsData as $stats) {
            $monthlyStats = $stats->monthly_stats;

            if (!$monthlyStats) {
                return response()->json(['error' => 'Le champ monthly_stats est vide ou invalide.'], 400);
            }

            $organizationPredictions = [];

            foreach ($metrics as $metric) {
                $samples = [];
                $targets = [];

                foreach ($monthlyStats as $month => $stat) {
                    $samples[] = [$month];
                    $targets[] = $stat[$metric];
                }

                $regression = new LeastSquares();
                $regression->train($samples, $targets);

                for ($month = 12; $month <= 24; $month++) {
                    $predictedValue = $regression->predict([$month]);
                    $organizationPredictions[$month][$metric] = $predictedValue;
                }
            }

            $predictions[$stats->organization] = $organizationPredictions;
        }

        return response()->json([
            'predictions' => $predictions,
        ]);
    }
}
