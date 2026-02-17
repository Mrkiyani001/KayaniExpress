<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $otp;
    public int $expiresIn; // minutes

    /**
     * Create a new notification instance.
     *
     * @param string $otp
     * @param int $expiresIn OTP expiration in minutes (default 10)
     */
    public function __construct(string $otp, int $expiresIn = 10)
    {
        $this->otp = $otp;
        $this->expiresIn = $expiresIn;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your OTP Verification Code')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your verification code is **{$this->otp}**.")
            ->line("This code will expire in {$this->expiresIn} minutes.")
            ->line('If you did not request this code, please ignore this email.')
            ->salutation('Thank you, The Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'otp' => $this->otp,
            'expires_in' => $this->expiresIn,
        ];
    }
}
