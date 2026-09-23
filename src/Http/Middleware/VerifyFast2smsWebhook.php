<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use function in_array;

use function is_array;
use function is_string;

use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates inbound Fast2sms webhooks.
 *
 * Fast2sms does not sign webhook requests, so authenticity relies on a shared
 * secret embedded in the configured URL (or sent as a header / query param) and,
 * optionally, an allow-list of source IPs. The secret is compared in constant
 * time and treated as a credential.
 */
class VerifyFast2smsWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->secretMatches($request) || ! $this->ipAllowed($request)) {
            abort(403, 'Invalid Fast2sms webhook signature.');
        }

        return $next($request);
    }

    private function secretMatches(Request $request): bool
    {
        $expected = config('fast2sms.webhook.secret');

        // A webhook with no configured secret is never trusted.
        if (! is_string($expected) || $expected === '') {
            return false;
        }

        $provided = $request->route('secret')
            ?? $request->header('X-Webhook-Secret')
            ?? $request->query('secret');

        return is_string($provided) && hash_equals($expected, $provided);
    }

    private function ipAllowed(Request $request): bool
    {
        $allowed = config('fast2sms.webhook.allowed_ips', []);

        if (! is_array($allowed) || $allowed === []) {
            return true;
        }

        return in_array($request->ip(), $allowed, true);
    }
}
