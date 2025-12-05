<?php

namespace App\Console\Commands;

use App\Models\LinkedInToken;
use Illuminate\Console\Command;
use App\Http\Controllers\LinkedInMyPageController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class updateOrganization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:update-organization';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch my organization data from LinkedIn and store them in the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
    $user = User::latest()->first();

    if (!$user) {
        Log::error('No user found in the database.');
        $this->error('No user found!');
        return 1; 
    }

    $accessToken = $user->linkedin_token ?? null;
        

        if (!$accessToken) {
            Log::error("Missing LinkedIn access token for user: " . $user->id);
            $this->error('Missing LinkedIn access token.');
            return 1; 
        }

        $controller = new LinkedInMyPageController();
        $controller->getUserOrganizations($accessToken);
        $controller->UpdateMyStatsOrganizations();
        $this->info('Organization data has been fetched and stored successfully.');
        return 0;
    }
}
