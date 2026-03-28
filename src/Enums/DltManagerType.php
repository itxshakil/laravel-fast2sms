<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Enums;

enum DltManagerType: string
{
    case SENDER = 'sender';

    case TEMPLATE = 'template';
}
