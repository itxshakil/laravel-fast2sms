# Contributing to Laravel Fast2SMS

First off, thank you for considering contributing to Laravel Fast2SMS! Your contributions help make this package better for everyone.

## Code of Conduct

Our Code of Conduct governs this project and everyone participating in it. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates. Include as many details as possible:

* Clear and descriptive title
* Steps to reproduce the problem
* Expected vs observed behavior
* PHP and package versions
* Minimal reproducible example if possible

### Suggesting Enhancements

If you have a suggestion for the project:

* Clear, descriptive title
* Detailed explanation of the enhancement
* Why it’s useful
* Any additional context or screenshots

### Pull Requests

1. Fork the repository
2. Create a branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests: `composer test`
5. Ensure code style: `composer lint`
6. Commit changes: `git commit -m 'Add amazing feature'`
7. Push branch: `git push origin feature/amazing-feature`
8. Open a Pull Request

#### Development Prerequisites

* PHP 8.3 or higher
* Composer
* Laravel 12.x or higher

#### Coding Standards

* Follow PSR-12 coding standards
* Write PHPUnit tests for new features
* Maintain existing test coverage
* Document new code using PHPDoc blocks
* Keep the codebase clean and maintainable

### Development Setup

```bash
git clone https://github.com/itxshakil/laravel-fast2sms.git
composer install
composer test
```

## Pull Request Guidelines

* Update `README.md` if the interface changes
* Update `CHANGELOG.md` via PR notes (labels are automatically mapped to changelog categories)
* The PR must work for PHP 8.3+
* Include tests for new features
* Follow existing coding style
* Use clear, descriptive commit messages

### Label Guidance (for Automatic Changelog)

Your PR labels determine how changes are grouped in release notes:

| Label              | Purpose / Changelog Category       |
| ------------------ | ---------------------------------- |
| `feature` / `core` | New features / core API            |
| `http`             | HTTP client, responses, exceptions |
| `notifications`    | Notification channel updates       |
| `queue`            | Jobs and queued SMS handling       |
| `config`           | Configuration updates              |
| `breaking-change`  | ⚠️ Breaking changes                |
| `tests`            | 🧪 Test additions                  |
| `ci` / `tooling`   | 🔧 CI or tooling updates           |
| `dependencies`     | ⬆️ Dependency updates              |
| `documentation`    | 📝 Docs updates                    |
| `meta`             | 🧹 Maintenance / housekeeping      |

> The **Release Drafter** workflow uses these labels to generate automatic release notes grouped by category.

---

## License

By contributing, your contributions are licensed under the same license as the project (MIT).

## Questions?

Don't hesitate to create an issue for any questions you might have.

---

Again, thank you for your contribution! 🚀
