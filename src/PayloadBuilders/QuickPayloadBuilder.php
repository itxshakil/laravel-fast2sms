<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\PayloadBuilders;

use Shakil\Fast2sms\Contracts\PayloadBuilderInterface;

class QuickPayloadBuilder implements PayloadBuilderInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function build(object $sms): array
    {
        return [
            'message' => $sms->getMessage(),
            'language' => $sms->getLanguage()->value,
        ];
    }
}
