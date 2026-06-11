<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;

class DocumentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $submission;
    public $downloadUrl;

    public function __construct($order, $submission)
    {
        $this->order = $order;
        $this->submission = $submission;
    }

    public function build()
    {
        $this->downloadUrl = URL::signedRoute(
            'download.document',
            ['submission' => $this->submission->id]
        );

        return $this->subject('Your Document is Ready')
            ->view('emails.document_download');
    }
}
