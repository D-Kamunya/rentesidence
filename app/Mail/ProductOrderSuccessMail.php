<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductOrderSuccessMail extends Mailable
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
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function build()
    {
        return $this->view('mail.product-order-success')
            ->subject($this->subject)
            ->with('content', $this->content);
    }
}
