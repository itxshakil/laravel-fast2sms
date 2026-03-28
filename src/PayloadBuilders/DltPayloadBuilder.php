<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\PayloadBuilders;

use Shakil\Fast2sms\Contracts\PayloadBuilderInterface;

class DltPayloadBuilder implements PayloadBuilderInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function build(object $sms): array
    {
        return [
            'sender_id' => $sms->getSenderId(),
            'message' => $sms->getMessage(),
            'entity_id' => $sms->getEntityId(),
            'template_id' => $sms->getTemplateId(),
            'variables_values' => $sms->getVariablesValues(),
        ];
    }
}
