<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\PayloadBuilders;

use Shakil\Fast2sms\Contracts\PayloadBuilderInterface;

class OtpPayloadBuilder implements PayloadBuilderInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function build(object $sms): array
    {
        return [
            'variables_values' => $sms->getMessage(),
        ];
    }
}
