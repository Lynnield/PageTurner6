<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public string $date
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Daily Sales Report - {$this->date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "<p>Attached is the daily sales report for {$this->date}.</p>",
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as("daily_sales_{$this->date}.xlsx")
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
