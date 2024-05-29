<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $address = config('bigmelo.mail.from_address');
        $subject = 'Support - Contact from web form';
        $name = config('bigmelo.mail.from_name');

        return $this->view('emails.support')
                    ->from($address, $name)
                    ->cc($address, $name)
                    ->bcc($address, $name)
                    ->replyTo($address, $name)
                    ->subject($subject)
                    ->with([
                        'name'              => $this->data['name'],
                        'lead_email'        => $this->data['lead_email'],
                        'forms_email'       => $this->data['forms_email'],
                        'lead_id'           => $this->data['lead_id'],
                        'user_id'           => $this->data['user_id'],
                        'support_message'   => $this->data['support_message'],
                    ]);
    }
}
