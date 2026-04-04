<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $email = $this->view('mail.template') // Replace with your view
                    ->with('data', $this->data);
                    if(isset($this->data['link'])){
                        $email = $email->attachment($this->data['link']);
                    }
                    return $email;
    }
}
