<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSponsorNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $sponsorName,
        private readonly float $donationPerLap,
        private readonly float $staticMax,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $donationText = $this->donationPerLap > 0
            ? number_format($this->donationPerLap, 2, ',', '.').' € pro Runde'
            : '';

        if ($this->staticMax > 0) {
            $maxText = number_format($this->staticMax, 2, ',', '.').' € (Maximal-/Festbetrag)';
            $donationText = $donationText ? "$donationText, max. $maxText" : $maxText;
        }

        return (new MailMessage)
            ->subject('Neuer Sponsor: '.$this->sponsorName)
            ->line($this->sponsorName.' hat sich als Sponsor eingetragen.')
            ->line('Zusage: '.$donationText)
            ->line('Vielen Dank für dein Engagement!');
    }
}
