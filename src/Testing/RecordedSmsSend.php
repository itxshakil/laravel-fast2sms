<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Testing;

use DateTimeImmutable;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;

/**
 * Represents a single recorded SMS send captured during faking.
 */
readonly class RecordedSmsSend
{
    public function __construct(
        public SmsParameters $parameters,
        public DateTimeImmutable $sentAt,
    ) {}
}
