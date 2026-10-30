<?php

namespace App\Modules\Chaquetas\Mail;

use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Correo al cliente con el número y el enlace de su cotización. */
class QuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        $brand = Setting::get('brand_name', "G'Lauren");
        $from  = config('mail.from.address') ?: config('mail.mailers.smtp.username') ?: 'no-reply@chaquetasdeprom.com';
        $reply = Setting::get('contact_email');

        return new Envelope(
            from: new Address($from, $brand),
            replyTo: $reply && filter_var($reply, FILTER_VALIDATE_EMAIL) ? [new Address($reply, $brand)] : [],
            subject: "Tu cotización {$this->quote->code()} · {$brand}",
        );
    }

    public function content(): Content
    {
        $wa = preg_replace('/\D/', '', (string) (Setting::get('contact_whatsapp') ?: config('chaquetas.whatsapp')));

        return new Content(
            view: 'emails.quote',
            with: [
                'brand'   => Setting::get('brand_name', "G'Lauren"),
                'quote'   => $this->quote,
                'summary' => $this->quote->summary(),
                'url'     => $this->quote->publicUrl(),
                'waUrl'   => $wa ? 'https://wa.me/'.$wa.'?text='.rawurlencode("¡Hola! Tengo una pregunta sobre la cotización {$this->quote->code()}.") : null,
                'baseUrl' => rtrim(config('app.url'), '/'),
            ],
        );
    }
}
