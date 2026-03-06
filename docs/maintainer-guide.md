# Maintainer Guide

This guide is for the package maintainer (or anyone picking this up after a break). It covers how the project is structured, how to develop and test changes, how to cut a release, and what every `composer` script does.

---

## Table of Contents

- [Project Architecture](#project-architecture)
- [Development Workflow](#development-workflow)
- [QA Commands Cheat-Sheet](#qa-commands-cheat-sheet)
- [CI/CD Pipeline](#cicd-pipeline)
- [Release Process](#release-process)
- [Key Files Reference](#key-files-reference)
- [See Also](#see-also)

---

## Project Architecture

### Namespace & Directory Map

| Path | Purpose |
|------|---------|
| `src/` | All production PHP (`Shakil\Fast2sms` namespace) |
| `src/Contracts/` | Interfaces defining the public API surface |
| `src/Traits/` | Composable behaviour mixed into the main service |
| `src/Clients/` | HTTP transport (`HttpClient`) and fake/log (`LogClient`) |
| `src/Enums/` | Typed enumerations (`SmsRoute`, `SmsLanguage`, `WhatsAppType`, `DltManagerType`) |
| `src/Responses/` | Response value objects returned from API calls |
| `src/Support/` | Standalone helpers (e.g. `SendRateThrottle`) |
| `src/BaseFast2smsService.php` | Core service class that composes all traits |
| `src/Fast2smsServiceProvider.php` | Laravel service provider (auto-discovered) |
| `src/Facades/Fast2sms.php` | Laravel facade |
| `config/` | Package config file (`fast2sms.php`) |
| `database/migrations/` | Optional migration for DB logging (`fast2sms_logs` table) |
| `tests/Unit/` | Pure unit tests (no Laravel boot) |
| `tests/Feature/` | Feature tests using Orchestra Testbench |
| `docs/` | User-facing documentation |
| `plans/` | Internal planning documents (excluded from Composer archives) |

### Design Patterns

**Trait composition** — behaviour is split into focused traits and mixed into `BaseFast2smsService`:

| Trait | Responsibility |
|-------|---------------|
| `ManagesSms` | Core SMS send pipeline (dedup → validate → throttle → balance gate → batch → send) |
| `ManagesSmsValidation` | Recipient validation, deduplication, chunking |
| `ManagesWhatsAppActions` | WhatsApp session and Meta message sends |
| `HandlesFaking` | `Fast2sms::fake()` support for tests |
| `HasQueueing` | Queue dispatch helpers |
| `ManagesAccount` | Wallet balance and account info |

**Contract-first** — every major capability has a corresponding interface in `src/Contracts/`. When adding a new public method, always update the interface first.

**Response objects** — API responses are wrapped in typed value objects via `ResponseFactory`. Never return raw arrays from public methods.

**Fake/Log client** — `LogClient` enables testing without real HTTP calls. Activated via `Fast2sms::fake()` in tests.

### Request Flow

```
Facade (Fast2sms::quick(), Fast2sms::otp(), Fast2sms::dlt())
  → ServiceProvider (resolves BaseFast2smsService)
    → BaseFast2smsService (composes traits)
      → ManagesSms::executeSend()
        → ManagesSmsValidation (dedup, strip invalid, chunk)
        → SendRateThrottle::check()
        → balance gate check
        → HttpClient (or LogClient in fake mode)
          → ResponseFactory::make()
            → SmsResponse / DltManagerResponse
```

---

## Development Workflow

### Prerequisites

- PHP 8.3 / 8.4 / 8.5
- Composer
- Laravel 11.x or higher (`^11.0 | ^12.0 | ^13.0`)
- A Fast2SMS account (for manual integration testing only)

### Branching Strategy

- `main` — production-ready code; every merge here triggers the release workflow.
- Feature branches — `feature/short-description`
- Bug fix branches — `fix/short-description`
- Use conventional commit messages (see below) so semantic-release can determine the next version automatically.

### Commit Message Conventions

This project uses **semantic-release** with the `@semantic-release/commit-analyzer` plugin. The commit type determines the version bump:

| Commit prefix | Version bump | Example |
|---------------|-------------|---------|
| `fix:` | Patch (0.0.x) | `fix: handle empty recipient list` |
| `feat:` | Minor (0.x.0) | `feat: add balance gate guard` |
| `feat!:` or `BREAKING CHANGE:` | Major (x.0.0) | `feat!: remove deprecated sendRaw()` |
| `docs:`, `chore:`, `refactor:`, `test:`, `style:` | No release | `docs: update installation guide` |

### Adding a New Feature — Checklist

1. Read the relevant contract in `src/Contracts/` first.
2. Identify the right trait (SMS → `ManagesSms`/`ManagesSmsValidation`; WhatsApp → `ManagesWhatsAppActions`; testing → `HandlesFaking`).
3. Check `src/Enums/` — prefer a new enum value over a string constant.
4. If the feature returns data from the API, add a response class in `src/Responses/` and register it in `ResponseFactory`.
5. Update the interface in `src/Contracts/` before touching the implementation.
6. Write a failing test first (`tests/Unit/` for pure logic, `tests/Feature/` for integration).
7. Implement the feature.
8. Run `composer qa` — all checks must pass before opening a PR.
9. Update `CHANGELOG.md` (the `[Unreleased]` section) and relevant docs.

### Code Style Rules

- **PHP 8.3+** — use named arguments, enums, readonly properties, match expressions.
- **PSR-4** — class files must match their namespace exactly.
- **Laravel Pint** (`pint.json`) enforces formatting — run `composer lint:fix` before committing.
- **Rector** (`rector.php`, level `UP_TO_PHP_83`) handles automated refactoring.
- **PHPStan level 6** (`phpstan.neon`) — do not introduce new violations; baseline is in `phpstan-baseline.neon`.
- Config values are always read via `config('fast2sms.*')` — never hardcode strings.

---

## QA Commands Cheat-Sheet

```bash
# Run the full test suite (PHPUnit)
composer test

# Static analysis only (PHPStan level 6)
composer analyse

# Auto-fix code style: Rector refactoring + Pint formatting
composer lint:fix

# Check code style without modifying files (analyse + Pint dry-run)
composer lint

# Full QA pipeline: lint → analyse → test — run before every PR
composer qa
```

All scripts are defined in `composer.json` under `"scripts"`. The `qa` script runs `lint` then `test` in sequence; a failure in any step stops the pipeline.

### Static Analysis Notes

- PHPStan is configured at **level 6** with the `larastan/larastan` extension.
- The baseline (`phpstan-baseline.neon`) suppresses pre-existing violations — do not add new ones.
- If you intentionally fix a baseline violation, remove it from the baseline file.
- Run `composer analyse` alone to see only static analysis output without running tests.

---

## CI/CD Pipeline

The CI pipeline is defined in `.github/workflows/ci.yml` and runs on every push and pull request to `main`.

### Jobs (in order)

| Job | Tool | What it checks |
|-----|------|---------------|
| `lint` | Laravel Pint | Code style (no modifications, exits non-zero on violations) |
| `analyse` | PHPStan level 6 | Static type correctness |
| `test` | PHPUnit 12 | Functional correctness across the full matrix |

### Test Matrix

The `test` job runs across:

- **OS**: `ubuntu-latest`, `macos-latest`, `windows-latest`
- **PHP**: `8.3`, `8.4`, `8.5`
- **Dependencies**: `prefer-lowest` (minimum supported versions), `prefer-stable` (latest stable)

This gives 18 combinations per run, ensuring the package works across all supported environments.

### Release Workflow

Defined in `.github/workflows/release.yml`. Triggered on pushes to `main` that match a version tag (`v*.*.*`). It:

1. Validates `composer.json`.
2. Runs `composer install --no-dev`.
3. Calls the release-drafter to generate/update the GitHub Release draft.

---

## Release Process

### How Versioning Works

This project uses **semantic-release** (configured in `.releaserc.json`). When a commit is merged to `main`:

1. `@semantic-release/commit-analyzer` reads commit messages and determines the next version (`patch` / `minor` / `major`).
2. `@semantic-release/changelog` updates `CHANGELOG.md`.
3. `@semantic-release/git` commits the updated `CHANGELOG.md` and `composer.json` back to `main`.
4. `@semantic-release/github` creates a GitHub Release with the generated release notes.

### How to Cut a Release

**Automated path (recommended):**

1. Merge your feature/fix PRs to `main` using conventional commit messages.
2. Semantic-release runs automatically on the CI and creates the release.

**Manual path (if needed):**

```bash
# 1. Ensure main is up to date and all tests pass
git checkout main && git pull
composer qa

# 2. Update CHANGELOG.md — move [Unreleased] items to a new [x.y.z] section
# 3. Commit with a conventional message
git add CHANGELOG.md
git commit -m "chore(release): prepare v2.1.0"

# 4. Tag the release
git tag v2.1.0
git push origin main --tags
```

The `release.yml` workflow fires on the tag push and creates the GitHub Release.

### Release Drafter

`.github/release-drafter.yml` auto-categorises PRs into release note sections based on labels:

| Label | Release note section |
|-------|---------------------|
| `feature`, `core`, `http` | 🚀 New Features |
| `fix`, `bugfix` | 🐛 Bug Fixes |
| `docs` | 📚 Documentation |
| `chore`, `dependencies` | 🧰 Maintenance |
| `breaking` | 💥 Breaking Changes |

Apply the correct label to every PR so release notes are generated correctly.

### What Gets Distributed

`.gitattributes` controls what Composer includes in the package archive. The following are **excluded** from `composer install` downloads (end-users never see them):

- `/docs/`, `/plans/`, `/tests/`, `/.github/`
- `CHANGELOG.md`, `CONTRIBUTING.md`, `UPGRADING.md`, `CODE_OF_CONDUCT.md`
- All config/tooling files (`phpstan*`, `pint.json`, `rector.php`, `phpunit.xml.dist`, `.releaserc.json`, etc.)

End-users receive only: `src/`, `config/`, `database/`, `composer.json`, `LICENSE.md`, `README.md`.

---

## Key Files Reference

| File | Purpose |
|------|---------|
| `composer.json` | Package metadata, dependencies, autoload, scripts |
| `phpunit.xml.dist` | PHPUnit 12 configuration (test suites, source paths) |
| `phpstan.neon` | PHPStan level 6 config with larastan extension |
| `phpstan-baseline.neon` | Suppressed pre-existing PHPStan violations |
| `pint.json` | Laravel Pint code style rules |
| `rector.php` | Rector automated refactoring config (PHP 8.3 level) |
| `.releaserc.json` | Semantic-release configuration |
| `.gitattributes` | Controls Composer archive contents and line endings |
| `.github/workflows/ci.yml` | Main CI pipeline (lint → analyse → test matrix) |
| `.github/workflows/release.yml` | Release validation and draft generation |
| `.github/workflows/stale.yml` | Auto-closes stale issues and PRs |
| `.github/release-drafter.yml` | Release note categories and PR label mapping |
| `config/fast2sms.php` | All package config keys with env-variable defaults |
| `tests/TestCase.php` | Base test case (Orchestra Testbench, env setup) |
| `src/BaseFast2smsService.php` | Core service — start here when tracing any feature |

### Config Key Groups

| Group | Env prefix | Feature |
|-------|-----------|---------|
| `fast2sms.api_key` | `FAST2SMS_API_KEY` | Authentication |
| `fast2sms.driver` | `FAST2SMS_DRIVER` | `http` or `log` (fake) |
| `fast2sms.deduplication.*` | `FAST2SMS_DEDUP_*` | Idempotency guard |
| `fast2sms.validation.*` | `FAST2SMS_STRIP_INVALID_*` | Recipient validation |
| `fast2sms.recipients.*` | `FAST2SMS_RECIPIENTS_*` | Dedup + batch size |
| `fast2sms.balance_gate.*` | `FAST2SMS_BALANCE_*` | Low-balance abort |
| `fast2sms.throttle.*` | `FAST2SMS_THROTTLE_*` | Per-minute rate limit |

---

## See Also

- [API Reference](api-reference.md)
- [Configuration](configuration.md)
- [Cost-Saving Features](cost-saving-features.md)
- [Error Handling](error-handling.md)
- [Testing](testing.md)
- [Upgrade Guide v1 → v2](upgrade-v1-to-v2.md)
- [CONTRIBUTING.md](../CONTRIBUTING.md)
- [CHANGELOG.md](../CHANGELOG.md)
