<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Notifications\Messages;

use Illuminate\Support\Collection;

use function is_array;

use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\ValidationException;
use Shakil\Fast2sms\Facades\Fast2sms;

use function sprintf;

use Stringable;

/**
 * SMS Message builder for Fast2SMS notifications.
 *
 * This class provides a fluent interface for building SMS messages with
 * various Fast2SMS specific features like DLT templates, sender IDs,
 * and language settings.
 *
 *
 * @property-read string|null $content The message content
 * @property-read string|null $templateId The DLT template ID
 * @property-read array<int, string>|null $variables Template variables
 * @property-read string|null $senderId Sender ID for the message
 * @property-read SmsRoute|null $route SMS route (QUICK/DLT/OTP)
 * @property-read SmsLanguage|null $language Message language
 * @property-read string|array<int, string|int>|Collection<int, string|int>|null $to Recipient number(s)
 * @property-read bool $flash Whether to send as a flash message
 * @property-read string|null $scheduleTime Scheduled send time
 * @property-read string|null $entityId DLT entity ID
 */
class SmsMessage implements Stringable
{
    /**
     * The message content.
     */
    protected ?string $content = null;

    /**
     * The recipient number(s).
     *
     * @var string|array<int, string|int>|Collection<int, string|int>|null
     */
    protected string|array|Collection|null $to = null;

    /**
     * The DLT template ID.
     */
    protected ?string $templateId = null;

    /**
     * The template variables.
     *
     * @var array<int, string>|null
     */
    protected ?array $variables = null;

    /**
     * The sender ID.
     */
    protected ?string $senderId = null;

    /**
     * The SMS route.
     */
    protected ?SmsRoute $route = null;

    /**
     * The message language.
     */
    protected ?SmsLanguage $language = null;

    /**
     * Whether to send as a flash message.
     */
    protected bool $flash = false;

    /**
     * The scheduled send time (ISO 8601 or Unix timestamp string).
     */
    protected ?string $scheduleTime = null;

    /**
     * The DLT entity ID.
     */
    protected ?string $entityId = null;

    public function __construct(string $content = '')
    {
        if ($content !== '' && $content !== '0') {
            $this->withContent($content);
        }
    }

    /**
     * Get a property value.
     *
     * @param  string $name Property name
     * @return mixed  The property value
     */
    public function __get(string $name): mixed
    {
        return $this->{$name} ?? null;
    }

    /**
     * Return a human-readable summary for logging.
     */
    public function __toString(): string
    {
        $to = match (true) {
            $this->to instanceof Collection => $this->to->implode(', '),
            is_array($this->to) => implode(', ', $this->to),
            default => $this->to ?? '',
        };

        $route = $this->route instanceof SmsRoute ? $this->route->value : 'default';
        $content = $this->content ?? ($this->templateId ? "template:$this->templateId" : '');

        return "SmsMessage(to: $to, route: $route, content: $content)";
    }

    /**
     * Named constructor for a fluent builder chain.
     *
     * @param string $content The message text
     */
    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    /**
     * Set the message content.
     *
     * @param  string $content The message text
     * @return $this
     *
     * @since 2.0.0
     */
    public function withContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @deprecated Use withContent() instead. Will be removed in v3.0.0.
     *
     * @param  string $content The message text
     * @return $this
     */
    public function content(string $content): self
    {
        trigger_error(
            sprintf('[Fast2SMS] %s::content() is deprecated; use withContent() instead. Will be removed in v3.0.0.', static::class),
            E_USER_DEPRECATED,
        );

        return $this->withContent($content);
    }

    /**
     * Set the DLT template and its variables.
     *
     * @param  string             $templateId The DLT template ID
     * @param  array<int, string> $variables  Variables to be replaced in the template
     * @return $this
     */
    public function template(string $templateId, array $variables = []): self
    {
        $this->templateId = $templateId;
        $this->variables = $variables;

        return $this;
    }

    /**
     * Set the sender ID.
     *
     * @param  string $senderId The sender ID
     * @return $this
     */
    public function from(string $senderId): self
    {
        $this->senderId = $senderId;

        return $this;
    }

    /**
     * Set the SMS route.
     *
     * @param  SmsRoute $route The route to use (QUICK/DLT/OTP)
     * @return $this
     *
     * @since 2.0.0
     */
    public function withRoute(SmsRoute $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @deprecated Use withRoute() instead. Will be removed in v3.0.0.
     *
     * @param  SmsRoute $route The route to use (QUICK/DLT/OTP)
     * @return $this
     */
    public function route(SmsRoute $route): self
    {
        trigger_error(
            sprintf('[Fast2SMS] %s::route() is deprecated; use withRoute() instead. Will be removed in v3.0.0.', static::class),
            E_USER_DEPRECATED,
        );

        return $this->withRoute($route);
    }

    /**
     * Mark the message as a flash SMS.
     *
     * @return $this
     */
    public function flash(bool $flash = true): self
    {
        $this->flash = $flash;

        return $this;
    }

    /**
     * Schedule the message for a future send time.
     *
     * @param  string $scheduleTime ISO 8601 datetime or Unix timestamp string
     * @return $this
     */
    public function schedule(string $scheduleTime): self
    {
        $this->scheduleTime = $scheduleTime;

        return $this;
    }

    /**
     * Set the DLT entity ID.
     *
     * @param  string $entityId The DLT entity ID
     * @return $this
     */
    public function entityId(string $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Set the message language.
     *
     * @param  SmsLanguage $language The language to use
     * @return $this
     */
    public function language(SmsLanguage $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Set the recipient's mobile number.
     *
     * @param  string|array<int, string|int>|Collection<int, string|int> $to Recipient number(s)
     * @return $this
     *
     * @since 2.0.0
     */
    public function withNumbers(string|array|Collection $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Set the recipient's mobile number.
     *
     * @param  string|array<int, string|int>|Collection<int, string|int> $to Recipient number(s)
     * @return $this
     *
     * @deprecated Use withNumbers() instead. Will be removed in v3.0.0.
     */
    public function to(string|array|Collection $to): self
    {
        trigger_error(
            sprintf('[Fast2SMS] %s::to() is deprecated; use withNumbers() instead. Will be removed in v3.0.0.', static::class),
            E_USER_DEPRECATED,
        );

        return $this->withNumbers($to);
    }

    /**
     * Validate the message before sending.
     *
     * @throws ValidationException
     */
    public function validate(): void
    {
        if ($this->content === null && $this->templateId === null) {
            throw ValidationException::emptyMessage();
        }

        if ($this->to === null) {
            throw ValidationException::missingRecipient();
        }
    }

    /**
     * Build an SmsParameters DTO from this message.
     *
     * @throws ValidationException
     */
    public function toSmsParameters(): SmsParameters
    {
        $this->validate();

        $numbers = match (true) {
            $this->to instanceof Collection => $this->to->all(),
            is_array($this->to) => $this->to,
            default => [$this->to],
        };

        return new SmsParameters(
            numbers: $numbers,
            message: $this->content ?? '',
            route: $this->route ?? SmsRoute::QUICK,
            language: $this->language,
            senderId: $this->senderId,
            entityId: $this->entityId,
            templateId: $this->templateId,
            variablesValues: $this->variables,
            flash: $this->flash,
            scheduleTime: $this->scheduleTime,
        );
    }

    /**
     * Return the character count of the message content.
     */
    public function charCount(): int
    {
        return mb_strlen($this->content ?? '');
    }

    /**
     * Determine whether the message contains non-GSM-7 (Unicode) characters.
     */
    public function isUnicode(): bool
    {
        return mb_strlen($this->content ?? '', 'ASCII') !== mb_strlen($this->content ?? '');
    }

    /**
     * Calculate the number of SMS credits this message will consume.
     *
     * GSM-7: 160 chars per single SMS, 153 per part in multi-part.
     * Unicode: 70 chars per single SMS, 67 per part in multi-part.
     */
    public function creditCount(): int
    {
        $len = $this->charCount();
        $single = $this->isUnicode() ? 70 : 160;
        $multi = $this->isUnicode() ? 67 : 153;

        if ($len <= $single) {
            return 1;
        }

        return (int) ceil($len / $multi);
    }

    /**
     * Determine whether the message will consume more than one SMS credit.
     */
    public function exceedsOneSms(): bool
    {
        return $this->creditCount() > 1;
    }

    /**
     * Send the message immediately.
     *
     * @throws Fast2smsException
     */
    public function send(): ResponseInterface
    {
        $this->validate();

        $service = Fast2sms::to($this->to);

        if ($this->route instanceof SmsRoute) {
            $service->route($this->route);
        }

        if ($this->senderId) {
            $service->senderId($this->senderId);
        }

        if ($this->language instanceof SmsLanguage) {
            $service->language($this->language);
        }

        if ($this->templateId) {
            $service->templateId($this->templateId)
                ->variables($this->variables ?? []);
        } else {
            $service->message((string) $this->content);
        }

        if ($this->flash) {
            $service->flash();
        }

        if ($this->scheduleTime !== null) {
            $service->schedule($this->scheduleTime);
        }

        if ($this->entityId !== null) {
            $service->entityId($this->entityId);
        }

        return $service->send();
    }
}
