<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OfficialCreatedNotification extends Notification
{
    use Queueable;

    public $official;

    /**
     * Create a new notification instance.
     *
     * @param User $official
     */
    public function __construct(User $official)
    {
        $this->official = $official;
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
            "official" => $this->official,
            "message"  => $notifiable->id === $this->official->id ? "You have been made an 
            official" : "A new official has been created",
            "icon"     => $this->official->picture,
            "type"     => "official_created"
        ];
    }
}
