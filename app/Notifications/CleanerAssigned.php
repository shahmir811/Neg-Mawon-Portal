<?php

namespace App\Notifications;

use App\Models\CleaningJob;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CleanerAssigned extends Notification
{
    public function __construct(
        public CleaningJob $job,
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
            ->subject('A cleaner has been assigned to your Nèg Mawon job')
            ->greeting("Good news, {$notifiable->name}!")
            ->line("We've matched a cleaner to your upcoming job at {$this->job->address}.")
            ->line('You can see their photo and your job details anytime from your dashboard.')
            ->action('View my dashboard', route('customer.dashboard'))
            ->line('Thank you for trusting Nèg Mawon Cleaning Services with your home.');
    }
}
