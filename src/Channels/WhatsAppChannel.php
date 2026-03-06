<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Channels;

use BadMethodCallException;
use Illuminate\Notifications\Notification;

use function is_string;

use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Facades\Fast2sms;

use function sprintf;

/**
 * WhatsApp Channel for Laravel notifications using Fast2SMS.
 */
class WhatsAppChannel
{
    /**
     * Send the given notification via WhatsApp.
     *
     * @param mixed        $notifiable   The entity receiving the notification
     * @param Notification $notification The notification instance
     *
     * @throws Fast2smsException When there's an error sending the message
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! $to = $notifiable->routeNotificationFor('whatsapp', $notification)) {
            return;
        }

        if (! method_exists($notification, 'toWhatsApp')) {
            throw new BadMethodCallException(
                sprintf('Method [toWhatsApp] missing from notification [%s].', $notification::class),
            );
        }

        $message = $notification->toWhatsApp($notifiable);

        if (is_string($message)) {
            Fast2sms::viaWhatsApp($to)->type(WhatsAppType::TEXT)->body($message)->send();

            return;
        }

        $service = Fast2sms::viaWhatsApp($message->to ?? $to);

        if ($message->fromPhoneNumberId) {
            $service->from($message->fromPhoneNumberId);
        }

        $type = $message->type ?? WhatsAppType::TEXT;
        $service->type($type);

        if ($message->templateId !== null) {
            $service->template($message->templateId);
        }

        if ($message->variables) {
            $service->variables($message->variables);
        }

        if ($message->mediaUrl) {
            $service->media($message->mediaUrl);
        }

        if ($message->documentFilename) {
            $service->documentFilename($message->documentFilename);
        }

        if ($message->components) {
            $service->components($message->components);
        }

        if ($message->content) {
            $service->body($message->content);
        }

        if ($message->interactive) {
            $service->interactive($message->interactive)->send();

            return;
        }

        if ($message->location) {
            $service->location(
                $message->location['latitude'],
                $message->location['longitude'],
                $message->location['name'] ?? null,
                $message->location['address'] ?? null,
            )->send();

            return;
        }

        if ($type === WhatsAppType::REACTION) {
            $service->messageId($message->messageId ?? '')->emoji($message->emoji ?? '')->send();

            return;
        }

        $service->send();
    }
}
