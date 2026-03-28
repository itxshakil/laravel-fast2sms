<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Enums;

/**
 * WhatsApp specific enums.
 */
enum WhatsAppType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case DOCUMENT = 'document';
    case AUDIO = 'audio';
    case VIDEO = 'video';
    case STICKER = 'sticker';
    case LOCATION = 'location';
    case REACTION = 'reaction';
    case INTERACTIVE = 'interactive';
}
