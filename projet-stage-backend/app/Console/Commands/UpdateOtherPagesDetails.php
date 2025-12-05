<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Log;

use Illuminate\Console\Command;
use App\Http\Controllers\FacebookOtherPagesDetails;

class UpdateOtherPagesDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-other-pages-details {facebookUserId} {pageName} {pageId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function handle()
    {
        $facebookUserId = $this->argument('facebookUserId');
        $pageName = $this->argument('pageName');
        $pageId = $this->argument('pageId');

        $controller = new FacebookOtherPagesDetails();

        $controller->getStoreDetails($facebookUserId , $pageName , $pageId);
         Log::info('data updated des pages: ');


        $this->info('Data fetched and stored successfully for page ID: ' . $pageId);
    }
}
