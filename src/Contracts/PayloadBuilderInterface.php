<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

interface PayloadBuilderInterface
{
    /**
     * Build the route-specific portion of the SMS API payload.
     *
     * @param  object               $sms The SMS service instance.
     * @return array<string, mixed>
     */
    public function build(object $sms): array;
}
