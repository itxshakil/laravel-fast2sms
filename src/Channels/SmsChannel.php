<?php
declare(strict_types=1);

namespace Shakil\Fast2sms\Channels;

use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Facades\Fast2sms;

/**
 * SMS Channel for Laravel notifications using Fast2SMS.
 *
 * This channel allows sending SMS notifications through Fast2SMS service.
 * It supports both simple string messages and complex message objects with
 * additional parameters like templates, sender IDs, and language settings.
 *
 * @package Shakil\Fast2sms\Channels
 */
class SmsChannel
{
    /**
     * Send the given notification via Fast2SMS.
     *
     * This method handles both simple string messages and SmsMessage objects.
     * For string messages, it uses the quick send feature.
     * For SmsMessage objects, it supports additional features like:
     * - DLT templates
     * - Custom sender IDs
     * - Language settings
     * - Route specification
     *
     * @param mixed $notifiable The entity receiving the notification
     * @param Notification $notification The notification instance
     *
     * @throws Fast2smsException When there's an error sending the SMS
     * @return void
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! $to = $notifiable->routeNotificationFor('sms', $notification)) {
            return;
        }

        $message = $notification->toSms($notifiable);

        if (\is_string($message)) {
            Fast2sms::quick($to, $message);
        } else {
            Fast2sms::to($to)
                ->route($message->route ?? SmsRoute::from(config('fast2sms.default_route')))
                ->senderId($message->senderId ?? config('fast2sms.default_sender_id'));

            if ($message->templateId !== null) {
                Fast2sms::route(SmsRoute::DLT)->templateId($message->templateId)
                    ->variables($message->variables ?? []);
            } else {
                Fast2sms::message($message->content);
            }

            if ($message->language !== null) {
                Fast2sms::language($message->language);
            }

            Fast2sms::send();
        }
    }
}
