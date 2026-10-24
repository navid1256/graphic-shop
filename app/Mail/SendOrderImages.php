<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOrderImages extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private array $sourceFiles, private string $customerName) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'فایل‌های خرید شما',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.sendOrderedImages',
            with: ['customerName' => $this->customerName],
        );
    }

    public function attachments(): array
    {
        return array_map(
            fn (string $path): Attachment => Attachment::fromStorageDisk('product_images', $path)
                ->as(basename($path)),
            $this->sourceFiles,
        );
    }
}
