<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LinkedInMyPageController;

class UpdateLinkedInData extends Command
{
    protected $signature = 'linkedin:update';
    protected $description = 'Récupérer les données LinkedIn à jour';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = new LinkedInMyPageController();
        $controller->getPageData('<page_id>'); 
    }
}
