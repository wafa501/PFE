<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FacebookPageReactionController;

class FacebookSavePagesPostsReactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:facebook-save-pages-posts-reactions {facebookUserId} {pageId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $facebookUserId = $this->argument('facebookUserId');
        $pageId = $this->argument('pageId');

        $controller = new FacebookPageReactionController();

        $controller->fetchAllPagesReactions($pageId, $facebookUserId);

        $this->info('Data fetched and stored successfully for page ID: ' . $pageId);
    }
}
