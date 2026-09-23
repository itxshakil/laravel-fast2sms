<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Enums;

use function mb_strtolower;
use function mb_trim;

/**
 * Normalised delivery state reported by a Fast2sms delivery-status webhook.
 */
enum DeliveryStatus: string
{
    case Delivered = 'delivered';

    case Failed = 'failed';

    case Pending = 'pending';

    /**
     * Map a raw Fast2sms status string onto a normalised case.
     *
     * Unknown or empty values fall back to Pending so an unexpected status
     * never throws while processing an inbound webhook.
     */
    public static function fromRaw(?string $status): self
    {
        return match (mb_strtolower(mb_trim((string) $status))) {
            'delivered', 'delivrd', 'success' => self::Delivered,
            'failed', 'undelivered', 'undeliv', 'rejected', 'expired' => self::Failed,
            default => self::Pending,
        };
    }

    /**
     * Whether this status represents a terminal successful delivery.
     */
    public function isDelivered(): bool
    {
        return $this === self::Delivered;
    }

    /**
     * Whether this status represents a terminal failure.
     */
    public function isFailed(): bool
    {
        return $this === self::Failed;
    }
}
