<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class PinGeneratedSuccessfullyNotification extends Notification
{
    use Queueable;

    public $user;
    public $election;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($election)
    {
        $this->election = $election;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            "election" => $this->election,
            "message" => "The pins have been generated successfully!",
            "generated_at" => Carbon::now()->format('Y-m-d H:i:s'),
            "type" => "pins_created",

        ];
    }
}
