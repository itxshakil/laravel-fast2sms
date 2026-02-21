<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use function is_array;

use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\WhatsAppType;

/**
 * Job for handling asynchronous WhatsApp sending through Fast2sms.
 */
class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly WhatsAppParameters $parameters,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppInterface $whatsapp): void
    {
        $whatsapp->to(is_array($this->parameters->to) ? implode(',', $this->parameters->to) : $this->parameters->to);

        if ($this->parameters->phoneNumberId) {
            $whatsapp->from($this->parameters->phoneNumberId);
        }

        if ($this->parameters->type) {
            $whatsapp->type(WhatsAppType::tryFrom($this->parameters->type) ?? WhatsAppType::TEXT);
        }

        if ($this->parameters->body) {
            $whatsapp->body($this->parameters->body);
        }

        if ($this->parameters->templateId) {
            $whatsapp->template($this->parameters->templateId);
        }

        if ($this->parameters->variables) {
            $whatsapp->variables($this->parameters->variables);
        }

        if ($this->parameters->mediaUrl) {
            $whatsapp->media($this->parameters->mediaUrl);
        }

        if ($this->parameters->documentFilename) {
            $whatsapp->documentFilename($this->parameters->documentFilename);
        }

        if ($this->parameters->components) {
            $whatsapp->components($this->parameters->components);
        }

        $whatsapp->send();
    }
}
