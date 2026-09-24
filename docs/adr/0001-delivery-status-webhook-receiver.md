# ADR-0001: Inbound Delivery-Status Webhook Receiver

**Status:** Accepted. Items 1-6 and 8-10 shipped in v2.1.0 (2026-09-23); item 7 shipped in the following release.
**Date:** 2026-07-09
**Deciders:** Shakil Alam (maintainer), package contributors

## Context

Fast2SMS accepts a send request and returns a `request_id`, but "accepted" is not "delivered." Whether an SMS/OTP/WhatsApp message actually reached the handset — and what it cost — only becomes known afterwards. Today the package has no way to surface that. `Fast2sms::otp()` sends a code the developer generates, `Fast2smsLog` records the *send* attempt, and there is no path for post-send delivery state to flow back in. "Did my OTP land, and why did it fail?" is the single most common support question for any transactional-messaging integration, and our users currently answer it by logging into the Fast2SMS dashboard manually.

Fast2SMS supports this natively. It pushes **real-time delivery-status (DLR) callbacks** for every channel — OTP, SMS, RCS, WhatsApp — as an HTTP `POST`/`GET` to a URL the account owner configures. The Standard payload carries `request_id`, `status` (`delivered`/`failed`), `failure_reason`, `amount_debited`, `sent_time`/`delivery_time`, `sms_count`, `channel`, `category`, and `udf1–udf3` correlation fields. Fast2SMS also exposes webhook-management CRUD APIs (create/update/delete/list per channel) and a pull-based fallback (Fetch Reports Manually, SMS Delivery Report API, SMS/WhatsApp Logs).

Forces at play:

- **Value:** closing the delivery loop is the strongest "painkiller" feature we can add — it makes failures diagnosable and spend attributable.
- **Security:** Fast2SMS webhooks do **not** ship HMAC request signing. The only practical authenticity control is a shared secret embedded in the URL the user configures (path segment or query param) plus optional source-IP allow-listing. The receiver must not trust the body blindly.
- **Framework fit:** a package that registers routes into a host app is intrusive; Laravel package users expect routes to be opt-in and the path to be configurable.
- **Consistency:** whatever we build must follow the existing conventions in `.claude/CLAUDE.md` — trait composition, contract-first, typed enums, `ResponseFactory` value objects, config-driven behaviour, events, and `Fast2sms::fake()`-based tests. The optional `Fast2smsLog` table already exists to be updated.
- **Idempotency:** Fast2SMS retries webhooks (`post_attempt` increments), so the same event can arrive more than once and out of order relative to the send record.

## Decision

Add an **opt-in, package-provided webhook receiver** that verifies a shared secret, parses the Fast2SMS DLR payload into a typed `DeliveryStatusResponse` value object, dispatches domain events, and (when database logging is enabled) reconciles the matching `Fast2smsLog` row by `request_id`.

Concretely:

- A new `ManagesWebhooks` trait backed by a `WebhookHandlerInterface` contract, exposing `Fast2sms::webhook()->handle(Request $request): DeliveryStatusResponse` plus thin wrappers over the Fast2SMS webhook-management CRUD (`registerWebhook`, `listWebhooks`, `deleteWebhook`).
- A `DeliveryStatus` enum (`Delivered`, `Failed`, `Pending`) mapping the raw `status` strings, consistent with existing enums (`SmsRoute`, `WhatsAppType`).
- A `DeliveryStatusResponse` value object produced through `ResponseFactory`, with `readonly` properties and explicit return types.
- New events `MessageDelivered` and `MessageFailed` (siblings of `SmsSent`/`SmsFailed`), each carrying the `DeliveryStatusResponse`.
- Config under `config/fast2sms.php` → `webhook` (`enabled`, `path`, `secret`, `auto_route`, `update_logs`, `allowed_ips`).
- An **auto-registered but disabled-by-default** route (`auto_route = false`), so nothing is mounted into the host app unless the user opts in. When enabled, the route is `POST {webhook.path}/{secret}`; the secret path segment is the primary authenticity check, enforced by a `VerifyFast2smsWebhook` middleware that also applies optional IP allow-listing.
- Idempotent reconciliation: match on `request_id`, apply status/`failure_reason`/`amount_debited`/`delivery_time`, and ignore an update whose `post_attempt`/`delivery_timestamp` is older than what is already recorded.

## Options Considered

### Option A: Batteries-included auto-wired route (opinionated)

Package registers the route, controller, middleware, events, and log reconciliation. User sets `webhook.enabled=true`, a secret, and points their Fast2SMS dashboard at the generated URL.

| Dimension | Assessment |
|-----------|------------|
| Complexity | Med — route macro, middleware, controller, parser, enum, response, events |
| Cost | Low runtime cost; some maintenance surface |
| Scalability | High — stateless controller, DB write is a single indexed update |
| Team familiarity | High — mirrors existing trait/contract/event patterns |

**Pros:** Best out-of-box experience; delivery tracking works with near-zero user code; reconciliation into the existing log is automatic; testable end-to-end via `Fast2sms::fake()`.
**Cons:** Registering routes from a package is intrusive if done carelessly; must be strictly opt-in; we own the security posture of an inbound public endpoint.

### Option B: Bring-your-own-route handler

Ship the `DeliveryStatusResponse` parser, `DeliveryStatus` enum, events, and a `WebhookHandler` service, but **no route**. The user writes their own controller/route and calls `Fast2sms::webhook()->handle($request)`.

| Dimension | Assessment |
|-----------|------------|
| Complexity | Low — no routing/middleware to own |
| Cost | Low |
| Scalability | High |
| Team familiarity | High |

**Pros:** Zero routing intrusion; user controls URL, auth, and middleware; smallest maintenance surface.
**Cons:** Every user re-implements the route, secret check, and CSRF/`VerifyCsrfToken` exclusion — exactly the boilerplate a package should remove; inconsistent security across installs; higher chance of misconfiguration (e.g. forgetting to exempt the route from CSRF).

### Option C: Event-only, no parsing or persistence

Package emits a raw `WebhookReceived` event with the untyped array; users parse and persist themselves.

| Dimension | Assessment |
|-----------|------------|
| Complexity | Very low |
| Cost | Low |
| Scalability | High |
| Team familiarity | Med |

**Pros:** Minimal code; maximally flexible.
**Cons:** Violates the package's "no raw arrays — wrap in a response object" rule; pushes parsing, enum mapping, idempotency, and log reconciliation onto every user; delivers little of the painkiller value. Effectively not a feature, just a passthrough.

## Trade-off Analysis

The core tension is **convenience vs. routing intrusion / security ownership**. Option C is rejected outright — it contradicts the codebase's contract-first, no-raw-arrays conventions and offloads the hard parts (idempotency, reconciliation, security) onto users, so it fails the "painkiller" bar.

Between A and B, the deciding factor is that the boilerplate B leaves behind (route registration, secret verification, CSRF exemption, log reconciliation) is precisely the error-prone, repeated work a first-class package should absorb — the same philosophy behind the existing cost-saving guards and fake/assert helpers. The intrusion risk of A is fully mitigated by making the route **opt-in and disabled by default**: nothing is mounted unless the user sets `webhook.enabled=true` and `auto_route=true`.

The chosen design is **A with B available underneath**: the auto-wired route is the happy path, but `Fast2sms::webhook()->handle($request)` is public, so advanced users who want their own route (custom auth, versioned API, queue-first ingestion) get Option B for free without re-implementing parsing or reconciliation. This maximizes value while preserving an escape hatch, at the cost of a moderately larger surface to test — acceptable given the fake-driven test strategy already in place.

Security is handled pragmatically given Fast2SMS provides no signing: a high-entropy secret in the URL path (not guessable, not logged in referers since it's a POST body target), optional IP allow-listing, and the route explicitly excluded from CSRF. This is documented as "secret-in-URL, treat it like a credential."

## Consequences

- **Easier:** users get delivery/failure state and true per-message cost with almost no code; `Fast2smsLog` becomes a source of truth for delivery, not just sends; new `MessageDelivered`/`MessageFailed` events plug into existing listeners; sets up a future reporting/observability layer.
- **Harder:** the package now owns a public inbound endpoint and its security posture; we must document the secret-in-URL model clearly and default it safely (disabled, no secret shipped). CSRF exemption and route caching interactions need explicit handling and tests.
- **To revisit:** if Fast2SMS later adds HMAC signing, add signature verification to the middleware and deprecate reliance on the URL secret. If pull-based reconciliation is needed for users who can't expose a public URL, add a `fast2sms:reconcile-reports` command over the Fetch-Reports/Logs APIs as a companion to (not replacement for) this receiver. Multi-channel nuances (RCS/WhatsApp payload differences) may require per-channel parser strategies if the Standard payload diverges.

## Action Items

1. [x] Add `WebhookHandlerInterface` to `src/Contracts/` (parse + handle + CRUD registration).
2. [x] Add `DeliveryStatus` enum and `DeliveryStatusResponse` value object; wire construction through `ResponseFactory`.
3. [x] Implement `ManagesWebhooks` trait; mix into `BaseFast2smsService`; expose via `Fast2sms` facade + regenerate IDE helper.
4. [x] Add `webhook` config block (`enabled`, `path`, `secret`, `auto_route`, `update_logs`, `allowed_ips`) with safe defaults (disabled, null secret).
5. [x] Add `VerifyFast2smsWebhook` middleware (secret compare via `hash_equals`, optional IP allow-list) and conditional route registration in `Fast2smsServiceProvider`; exempt from CSRF.
6. [x] Dispatch `MessageDelivered` / `MessageFailed`; add matching log-writing listeners; implement idempotent `Fast2smsLog` reconciliation by `request_id` guarded on `post_attempt`/`delivery_timestamp`.
7. [x] Extend `Fast2smsFake` with webhook assertions (`assertWebhookHandled`, `assertWebhookNotHandled`, `assertWebhookHandledCount`, `assertMessageDelivered`, `assertMessageFailed`, `handledWebhooks`).
8. [x] Tests: Unit (payload → `DeliveryStatusResponse` mapping, enum, idempotency guard) and Feature (route registration, secret rejection, CSRF exemption, log reconciliation) — no real HTTP.
9. [x] Docs: `docs/webhooks.md` (setup, secret-in-URL security model, dashboard config) + README section; note the pull-based fallback.
10. [x] Run `composer qa` (lint + PHPStan level 6 + tests) before merge.
