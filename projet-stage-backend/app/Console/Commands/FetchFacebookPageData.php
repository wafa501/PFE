<?php

namespace App\Console\Commands;
use App\Models\FacebookPage;
use Illuminate\Support\Facades\Log;

use Illuminate\Console\Command;
use App\Models\FacebookUsers; 
use App\Http\Controllers\FacebookPageDetailsController;

class FetchFacebookPageData extends Command
{
    protected $signature = 'facebook:fetch-page-data {pageId}';

    protected $description = 'Fetch and store Facebook page data for a given user and page ID';

    public function handle()
    {
           $pageId = $this->argument('pageId');

        $page = FacebookPage::where('page_id', $pageId)->first();

        if (!$page) {
            Log::error("Page not found with ID: {$pageId}");
            $this->error("No Facebook page found with ID {$pageId}");
            return 1;
        }

        $pageAccessToken = $page->page_access_token;

        if (!$pageAccessToken) {
            Log::error("Page access token missing for page: ".$pageId);
            $this->error('Missing page access token.');
            return 1;
        }

        Log::info('Facebook Page Access Token: ' . $pageAccessToken);

        $controller = new FacebookPageDetailsController();

        $controller->fetchAndStorePageData($pageId,$pageAccessToken);

        $this->info('Data fetched and stored successfully for page ID: ' . $pageId);
    }
}
