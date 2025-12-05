<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FanCountAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $page;
    public $difference;

    public function __construct($page, $difference)
    {
        $this->page = $page;
        $this->difference = $difference;
    }

    public function build()
    {
        return $this->view('emails.fan_count_alert')
                    ->subject('Alerte : Baisse significative de fans sur votre page Facebook')
                    ->with([
                        'pageName' => $this->page->name,
                        'difference' => $this->difference,
                    ]);
    }
}
