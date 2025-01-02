<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServerUnhealthyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $server;

    /**
     * Create a new notification instance.
     *
     * @param  $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail']; // Specify email as the notification channel
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
            ->subject('Server Unhealthy Alert')
            ->line("The server '{$this->server->name}' has become unhealthy.")
            ->line("URL: {$this->server->url}")
            ->line("Please check the server and take the necessary actions.")
            ->action('View Server', url('/servers/' . $this->server->id))
            ->line('This is an automated alert from the Server Monitoring system.');
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
            'server_id' => $this->server->id,
            'server_name' => $this->server->name,
            'status' => 'unhealthy',
        ];
    }
}
