<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyMail extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->name = $request->username;
        $this->message= $request ->message;
        $this->email =$request ->email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name = $this->name;
        $email = $this->email;
        $msg =  $this->message;

        return $this->subject($this->email)->view('message',compact('name','email','msg'));
    }
}
