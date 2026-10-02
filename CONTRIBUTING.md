# Contributing

Thanks for taking the time to help.

## Reporting bugs

Open an issue with the plugin version, WordPress version, PHP version, what you expected and what happened. If the problem is about sending, turn on **Enable Debug Log** in the plugin settings, reproduce it, and include the relevant log lines. The log already hides credentials and message content.

For security problems, follow [SECURITY.md](SECURITY.md) instead of opening an issue.

## Development setup

You need PHP 8.0 or later and [Composer](https://getcomposer.org/).

```
git clone https://github.com/M-Awais-irfan/xoauth-mailer.git
cd xoauth-mailer
composer install
```

| Command | What it does |
| --- | --- |
| `composer lint` | Coding standards check (security, i18n, prefixes, PHP compatibility) |
| `composer test` | Runs the tests in `tests/` |

Both run automatically on every pull request.

## Pull requests

- Keep each pull request focused on one change.
- Use the `XOAM_` prefix for classes and constants, `xoam_` for options and hooks, and the `xoauth-mailer` text domain for strings.
- Escape all output and sanitize all input.
- Add a line under **Unreleased** in `CHANGELOG.md`.
