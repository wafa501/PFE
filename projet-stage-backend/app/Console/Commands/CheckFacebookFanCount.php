<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use Illuminate\Console\Command;
use App\Models\FacebookPageDetail; 
use App\Models\FacebookPageFanHistory;
use Illuminate\Support\Facades\Mail;
use App\Models\FacebookUsers;
use App\Models\FacebookPage;

class CheckFacebookFanCount extends Command
{
    protected $signature = 'facebook:check-fan-count';
    protected $description = 'Check if fan count has decreased significantly';

    public function handle()
    {
        $this->info("Vérification du nombre de fans..."); 
        $timeLimit = now()->subHour(); 
        $pages = FacebookPageDetail::all(); 
    
        if ($pages->isEmpty()) {
            $this->info("Aucune page trouvée."); 
            return;
        }
    
        foreach ($pages as $page) {
            $lastHistory = FacebookPageFanHistory::where('page_id', $page->id)
                               ->orderBy('checked_at', 'desc')
                               ->first();
    
            if ($lastHistory && $lastHistory->checked_at > $timeLimit) {
                $difference = $lastHistory->fan_count - $page->fan_count;
                if ($difference > 0) { 
                    $this->sendFanCountAlert($page, $difference);
                } else {
                    $this->info("Pas de baisse de fans pour la page {$page->id}."); 
                }
            }
    
            FacebookPageFanHistory::create([
                'page_id' => $page->id,
                'fan_count' => $page->fan_count,
                'checked_at' => now(),
            ]);
        }
    
        $this->info("Vérification terminée."); 
    }
    
    protected function sendFanCountAlert($page, $difference)
{
    $pageRecord = FacebookPage::where('page_id', $page->fb_id)->first();

    if (!$pageRecord) {
        Log::warning("Aucune page Facebook correspondante trouvée pour page ID {$page->id}");
        return;
    }

    $user = FacebookUsers::find($pageRecord->facebook_user_id);

    if (!$user) {
        Log::warning("Aucun utilisateur trouvé pour la page ID {$page->id}. Alerte non envoyée.");
        return;
    }

   
    if (empty($user->email) || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
        Log::warning("Email invalide pour l'utilisateur ID {$user->id}. Alerte non envoyée.");
        return;
    }

    Log::info("Utilisateur trouvé : {$user->email}, envoi de l'alerte...");

    try {
        Mail::to($user->email)->send(new \App\Mail\FanCountAlert($page, $difference));
        $this->info("Alerte envoyée pour la page {$page->id} (baisse de {$difference} fans)");
    } catch (\Exception $e) {
        Log::error("Erreur lors de l'envoi de l'alerte pour la page {$page->id} : " . $e->getMessage());
    }
}
}