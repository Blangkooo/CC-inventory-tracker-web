<?php

namespace App\Mail;

use App\Models\DiscrepancyAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscrepancyAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public DiscrepancyAlert $alert) {}

    public function envelope(): Envelope
    {
        $severity = strtoupper($this->alert->severity);

        return new Envelope(
            subject: "[{$severity}] Discrepancy Alert — {$this->alert->branch->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.discrepancy-alert',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
