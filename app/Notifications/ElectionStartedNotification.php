<?php

namespace App\Notifications;

use App\Election;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class ElectionStartedNotification
 * @package App\Notifications
 */
class ElectionStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Election
     */
    protected $election;

    /**
     * Create a new notification instance.
     *
     * @param Election $election
     */
    public function __construct(Election $election)
    {
        $this->election = $election;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)->line('The introduction to the notification.')
                                ->action('Notification Action', url('/'))
                                ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            "election" => $this->election,
            "message" => "The election has started!",
            "type" => "election_started"
        ];
    }
}
