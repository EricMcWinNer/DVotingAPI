<?php

namespace App\Notifications;

use App\Candidate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CandidateCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $candidate;

    /**
     * Create a new notification instance.
     *
     * @param Candidate $candidate
     */
    public function __construct(Candidate $candidate)
    {
        $this->candidate = $candidate;
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
        return (new MailMessage)->line('The introduction to the notification.')->action('Notification Action', url('/'))
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
            "user_id"    => $this->candidate->user_id,
            "name"       => $this->candidate->name,
            "id"         => $this->candidate->id,
            "picture"    => $this->candidate->candidate_picture,
            "created_at" => $this->candidate->created_at,
            "message"    => "You have been made a candidate",
        ];
    }
}
