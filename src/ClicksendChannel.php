<?php

namespace NotificationChannels\Clicksend;

use ClickSend\Api\SMSApi;
use ClickSend\ApiException;
use ClickSend\Model\SmsMessage;
use ClickSend\Model\SmsMessageCollection;
use NotificationChannels\Clicksend\Exceptions\CouldNotSendNotification;
use Illuminate\Notifications\Notification;

class ClicksendChannel
{
    /**
     * @var SMSApi
     */
    protected $api;

    public function __construct(SMSApi $api)
    {
        $this->api = $api;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @return string
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification): string
    {
        if (
            ! method_exists($notifiable, 'routeNotificationFor')
            || blank($route = $notifiable->routeNotificationFor('sms', $notification))
        ) {
            throw CouldNotSendNotification::serviceRespondedWithAnError('Route not found');
        }

        $message = method_exists($notification, 'toClicksend')
            ? $notification->toClicksend($notifiable)
            : method_exists($notification, 'toSms')
            ? $notification->toSms($notifiable)
                : null;

        if (is_string($message)) {
            $message = (new SmsMessage())
                ->setSource('php')
                ->setBody($message)
                ->setTo($route);
        }
        if (! $message instanceof SmsMessage) {
            throw CouldNotSendNotification::serviceRespondedWithAnError('No message configured');
        }

        try {
            return $this->api->smsSendPost(
                (new SmsMessageCollection())
                    ->setMessages([$message])
            );
        } catch (ApiException $e) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($e->getMessage());
        }
    }
}
