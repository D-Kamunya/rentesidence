<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RentPaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;
    public $content, $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content)
    {
        $this->content = $content;
        $this->subject = $content['subject'];
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->view('mail.rent-payment-success')
            ->subject($this->subject)
            ->with('content', $this->content);
    }
}
