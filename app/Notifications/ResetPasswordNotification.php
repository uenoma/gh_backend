<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim(config('app.frontend_url', config('app.url')), '/');
        $url = $frontendUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('パスワードリセットのご案内')
            ->line('パスワードリセットのリクエストを受け付けました。')
            ->action('パスワードをリセットする', $url)
            ->line('このリンクは ' . config('auth.passwords.users.expire', 60) . ' 分後に失効します。')
            ->line('パスワードリセットをリクエストしていない場合は、このメールを無視してください。');
    }
}
