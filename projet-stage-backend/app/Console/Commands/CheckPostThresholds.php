<?php

namespace App\Console\Commands;
use App\Models\LinkedInToken;

use Illuminate\Console\Command;
use App\Http\Controllers\NotificationController;

class CheckPostThresholds extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:check-thresholds';
     /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check posts for thresholds and create notifications if exceeded';

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
        $tokenRecord = LinkedInToken::find(1); 
        $accessToken = $tokenRecord ? $tokenRecord->access_token : null;
        $controller = new NotificationController();
        $controller->checkPostThresholds();
    }
}
