<?php

namespace App\Modules\Chaquetas\Mail;

use App\Modules\Chaquetas\Models\Appointment;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CustomerReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public ?Appointment $appointment = null,
    ) {}

    public function envelope(): Envelope
    {
        $brand = Setting::get('brand_name', 'Glauren');
        $from  = config('mail.from.address') ?: config('mail.mailers.smtp.username') ?: 'no-reply@chaquetasdeprom.com';

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($from, $brand),
            subject: Setting::get('contact_reply_subject') ?: "¡Gracias por escribirnos! · {$brand}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-reply',
            with: [
                'brand'   => Setting::get('brand_name', 'Glauren'),
                'body'    => Setting::get('contact_reply_message', $this->defaultBody()),
                'name'    => $this->customerName,
                'appt'    => $this->appointment,
                'baseUrl' => rtrim(config('app.url'), '/'),
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = Setting::get('contact_reply_pdf');
        if ($pdf && Storage::disk('public')->exists($pdf)) {
            return [\Illuminate\Mail\Mailables\Attachment::fromStorageDisk('public', $pdf)
                ->as('Glauren.pdf')->withMime('application/pdf')];
        }

        return [];
    }

    private function defaultBody(): string
    {
        return "¡Hola! Gracias por tu interés en nuestras chaquetas y busos de promoción. "
            ."Adjuntamos la información. Cuando quieras, diseña la tuya en 3D o agenda una reunión con nosotros. ¡Estamos para ayudarte!";
    }
}
