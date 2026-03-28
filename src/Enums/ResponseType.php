<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Enums;

enum ResponseType: string
{
    case Sms = 'sms';
    case WalletBalance = 'wallet';
    case DltManager = 'dlt_manager';
    case WhatsApp = 'whatsapp';
    case Generic = 'generic';
}
