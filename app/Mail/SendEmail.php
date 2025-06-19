<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */


    public $subject;
    public $message;
    public $attachments;

    public function __construct($subject, $message, $attachments=[])
    {
        $this->message = $message;
        $this->subject = $subject;
        $this->attachments = is_array($attachments) ? $attachments : [$attachments];
    }

    public function build()
    {
        $email = $this->subject($this->subject)
            ->view('emails.template', [
                'subjectS' => $this->subject,
                'messageS' => $this->message,
            ]);

        // \Log::info("Attachments data:", ['attachments' => $this->attachments]);
        
        // if (!empty($this->attachments)) {
        //     foreach ($this->attachments as $file) {
        //         if (is_string($file) && file_exists($file)) {
        //             $email->attach($file);
        //         }
        //     }
        // }

        return $email;
    }


}
