<?php

namespace App\Modules\Chaquetas\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo sencillo con la marca: título, texto, filas opcionales y un botón.
 * Se usa para el resumen diario de ventas, el seguimiento automático y la encuesta.
 */
class SimpleMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array<int, array{title:string, sub?:string, url?:string, tag?:string}> $rows */
    public function __construct(
        public string $subjectLine,
        public string $heading,
        public string $body,
        public array $rows = [],
        public ?string $buttonText = null,
        public ?string $buttonUrl = null,
        public ?string $kicker = null,
    ) {}

    public function envelope(): Envelope
    {
        $from = config('mail.from.address') ?: config('mail.mailers.smtp.username') ?: 'no-reply@chaquetasdeprom.com';

        return new Envelope(from: new Address($from, config('app.name', 'Glauren')), subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.simple');
    }
}
