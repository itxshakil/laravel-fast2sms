<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

use Shakil\Fast2sms\Enums\DltManagerType;

/**
 * Interface for account management operations.
 */
interface AccountManagerInterface
{
    /**
     * Check the wallet balance.
     */
    public function checkBalance(?float $threshold = null): ResponseInterface;

    /**
     * Access the DLT manager.
     *
     * @param DltManagerType $type The DLT manager type.
     */
    public function dltManager(DltManagerType $type): ResponseInterface;
}
