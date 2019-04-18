<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class VerifyUpdateEmailNotification extends Notification
{
    // TODO: 英語で送信されるので、このあたりで修正する　もしくは locale ？

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('メールアドレス変更リンク通知')
            ->line('メールアドレスを変更するには下記のボタンをクリックしてください。')
            ->action(
                'メールアドレス変更',
                $this->verificationUrl($notifiable)
            )
            ->line('もしこのメールに心当たりがない場合、このメールを破棄いただきますようお願いします。');
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $email = $notifiable->routeNotificationFor('mail');
        $url = URL::temporarySignedRoute(
            'api.account.email.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            ['email' => $email]
        );
        $url = str_replace('/api/account/verify', '/pages/account/verify', $url);
        return $url;
    }
}
