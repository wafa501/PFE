<?php

namespace App\Console\Commands;
use App\Models\LinkedInToken;
use App\Models\User;
use Illuminate\Console\Command;
use App\Http\Controllers\LinkedInOtherPageController;

class updateOtherPostsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:update-other-posts-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch other LinkedIn posts and store them in the database';

    
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
    public function handle(LinkedInOtherPageController $controller)
    {
    $user = User::first(); 
    $accessToken = $user->linkedin_token;

    $controller->showDetails('soprahr', '18931', $accessToken);


        
        $this->info('Other organizations posts have been fetched and stored successfully.');
        return 0;
    }
    
    
}