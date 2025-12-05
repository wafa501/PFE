<?php

namespace App\Console\Commands;
use App\Models\LinkedInToken;
use App\Models\User;

use Illuminate\Console\Command;
use App\Http\Controllers\StatisticsController;

class UpdateStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch LinkedIn statistics and store them in the database';

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
    public function handle(StatisticsController $controller)
    {
         $user = User::first(); 
    $accessToken = $user->linkedin_token;

        $this->info('Fetching stats for Sopra HR Software...');
        $controller->showAllYears('18931',$accessToken);
        $this->info('Fetching stats for vermeg...');
        $controller->showAllYears('38256',$accessToken);
        $this->info('Fetching stats for adp...');
        $controller->showAllYears('1463',$accessToken);
        $this->info('Fetching stats for teamlink...');
        $controller->showAllYears('71370828',$accessToken);
        $this->info('Fetching stats for lynx...');
        $controller->showAllYears('78338695',$accessToken);

        $this->info('Other organizations stats have been fetched and stored successfully.');
        return 0;
    }
}
