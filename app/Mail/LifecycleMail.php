<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Generic CS lifecycle mail: renders the reusable mail.lifecycle body (which extends the
 * CS brand layout) from a structured data array. Every lifecycle email uses this — there is
 * no per-mail hand-rolled HTML, so nothing can render off-brand.
 */
class LifecycleMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $m;

    public function __construct(string $subject, array $data)
    {
        $data['subject'] = $subject;
        $this->subject   = $subject;
        $this->m         = $data;
    }

    public function build()
    {
        return $this->view('mail.lifecycle')
            ->subject($this->subject)
            ->with('m', $this->m);
    }
}
