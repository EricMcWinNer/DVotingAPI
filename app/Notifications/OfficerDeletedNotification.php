<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OfficerDeletedNotification extends Notification
{
    use Queueable;

    public $officer;

    /**
     * Create a new notification instance.
     *
     * @param User $officer
     */
    public function __construct(User $officer)
    {
        $this->officer = $officer;
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
            "officer" => $this->officer,
            "message" => $notifiable->id === $this->officer->id ?
                "You have been removed from the officer list" : "A new officer has been created",
            "icon"    => $this->officer->picture,
            "type"    => "officer_deleted"
        ];
    }
}
