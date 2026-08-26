<?php

namespace App\Modules\Chaquetas\Mail;

use App\Modules\Chaquetas\Models\Appointment;
use App\Modules\Chaquetas\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminLeadMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lead $lead,
        public ?Appointment $appointment = null,
    ) {}

    public function envelope(): Envelope
    {
        $sub = '🔔 Nuevo contacto: '.$this->lead->name;
        if ($this->appointment) {
            $sub = '📅 Nueva cita: '.$this->lead->name.' · '.$this->appointment->date->format('d/m').' '.substr($this->appointment->time, 0, 5);
        }

        $from = config('mail.from.address') ?: config('mail.mailers.smtp.username') ?: 'no-reply@chaquetasdeprom.com';

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($from, config('app.name', 'Glauren')),
            subject: $sub,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-lead',
            with: ['lead' => $this->lead, 'appt' => $this->appointment],
        );
    }
}
