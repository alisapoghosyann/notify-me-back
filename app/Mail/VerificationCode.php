<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($res)
    {
         $this->code = $res;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $code= $this -> code;
        return $this->view('verificationcode',compact('code'));
    }
}

