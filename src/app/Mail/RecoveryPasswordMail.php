<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecoveryPasswordMail extends Mailable
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
        $subject = 'Bigmelo - Link de recuperación de contraseña';
        $name = config('bigmelo.mail.from_name');

        return $this->view('emails.recovery_password')
                    ->from($address, $name)
                    ->cc($address, $name)
                    ->bcc($address, $name)
                    ->replyTo($address, $name)
                    ->subject($subject)
                    ->with([
                        'link'  => $this->data['link'],
                    ]);
    }
}
