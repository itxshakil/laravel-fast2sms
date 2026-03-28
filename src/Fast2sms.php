<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Override;
use Psr\SimpleCache\InvalidArgumentException;
use Shakil\Fast2sms\Contracts\Fast2smsInterface;
use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\Exceptions\DuplicateSendException;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\InsufficientBalanceException;
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;
use Shakil\Fast2sms\Exceptions\ValidationException;
use Shakil\Fast2sms\Traits\ManagesAccount;
use Shakil\Fast2sms\Traits\ManagesSms;
use Throwable;

/**
 * Main service class for interacting with the Fast2sms API.
 *
 * This class provides methods for sending various types of SMS messages
 * such as Quick, DLT, and OTP, as well as checking wallet balance and
 * retrieving DLT manager details.
 */
class Fast2sms extends BaseFast2smsService implements Fast2smsInterface
{
    use ManagesAccount;
    use ManagesSms;

    /**
     * Send an SMS using the currently configured parameters.
     *
     * @throws DuplicateSendException
     * @throws InsufficientBalanceException
     * @throws ThrottleExceededException
     * @throws ValidationException
     * @throws Fast2smsException
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function send(): ResponseInterface
    {
        try {
            return $this->executeSend();
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Start a fluent WhatsApp message builder.
     *
     * @param string|array<int, string>|null $to
     */
    public function viaWhatsApp(string|array|null $to = null): WhatsAppInterface
    {
        $whatsapp = $this->whatsapp();
        if ($to !== null) {
            $whatsapp->to($to);
        }

        return $whatsapp;
    }

    /**
     * Access the WhatsApp service.
     */
    public function whatsapp(): WhatsAppInterface
    {
        return app('fast2sms.whatsapp');
    }

    /**
     * Hook method called after every API call.
     *
     * Used to reset SMS parameters for the next request.
     */
    #[Override]
    protected function afterApiCall(): void
    {
        $this->resetParameters();
    }
}
