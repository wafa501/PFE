<?php

namespace App\Console\Commands;
use App\Models\LinkedInToken;

use Illuminate\Console\Command;
use App\Http\Controllers\StatisticsController;

class updateFacebookPostsData extends Command
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

        $this->info('Fetching stats for Ooredoo...');
        $controller->showAllYears('166536');

        $this->info('Fetching stats for Orange Tunisie...');
        $controller->showAllYears('947572');

        $this->info('Fetching stats for Tunisie Telecom...');
        $controller->showAllYears('421879');

        $this->info('Fetching stats for access...');
        $controller->showAllYears('1129172');

        
        $this->info('Other organizations stats have been fetched and stored successfully.');
        return 0;
    }
}
