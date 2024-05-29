<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportNotificationToUserMail extends Mailable
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
        $subject = '¡Mensaje enviado a Bigmelo!';
        $name = config('bigmelo.mail.from_name');

        return $this->view('emails.support_user_notification')
                    ->from($address, $name)
                    ->replyTo($address, $name)
                    ->subject($subject)
                    ->with(['support_message'   => $this->data['support_message']]);
    }
}
