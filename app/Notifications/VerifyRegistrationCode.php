<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyRegistrationCode extends Notification
{
    use Queueable;
    public function __construct(public string $code) {}
    public function via(object $notifiable): array { return ['mail']; }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Vermontshop email tasdiqlash kodi')->greeting('Vermontshop’ga xush kelibsiz!')->line('Ro‘yxatdan o‘tishni yakunlash uchun quyidagi kodni kiriting:')->line($this->code)->line('Kod 5 daqiqa amal qiladi. Agar bu siz bo‘lmasangiz, xabarni e’tiborsiz qoldiring.');
    }
}
