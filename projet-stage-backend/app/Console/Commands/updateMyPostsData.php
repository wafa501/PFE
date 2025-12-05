<?php

namespace App\Console\Commands;

use App\Models\LinkedInToken;
use Illuminate\Console\Command;
use App\Http\Controllers\LinkedInMyPostsController;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use Illuminate\Support\Facades\Log;

class updateMyPostsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:update-my-posts-data {userId?}'; // Accepte un userId en argument

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch my LinkedIn posts and store them in the database';

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

    // Check if the user has a LinkedIn token
    $accessToken = $user->linkedin_token ?? null;
    if (!$accessToken) {
        Log::error("Missing LinkedIn token for user: $userId");
        $this->error('Missing LinkedIn token!');
        return 1;
    }
    $userId = $user->id;
    // Log and display token information
    $this->info("Fetched token for user: $userId");

    // Call the LinkedInMyPostsController to fetch posts
    $controller = new LinkedInMyPostsController();
    $posts = $controller->showDetails($accessToken);
    if ($posts) {
        $user->linkedin_token = $accessToken; 
        $user->save(); 
        $this->info('Posts have been fetched and stored successfully.');
    } else {
        $this->error('Failed to fetch posts.');
        return 1;
    }

    return 0;
}

}
