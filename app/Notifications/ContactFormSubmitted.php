<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormSubmitted extends Notification
{
    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $message,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New quote request from {$this->senderName}")
            ->greeting('New lead from the Nèg Mawon website')
            ->line("**Name:** {$this->senderName}")
            ->line("**Email:** {$this->senderEmail}")
            ->line('**Message:**')
            ->line($this->message)
            ->line('Reply directly to this email to get back to them.');
    }
}
