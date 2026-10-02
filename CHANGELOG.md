# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-10-02

### Added

- Send all WordPress email through Google Workspace SMTP.
- Two ways to sign in: OAuth2 using Google's XOAUTH2 mechanism, or an App Password.
- OAuth2 connect flow with a REST API callback at `/wp-json/xoauth-mailer/v1/oauth-callback`.
- Automatic access token refresh, 60 seconds before expiry.
- Optional revoke at Google when disconnecting.
- `XOAM_APP_PASSWORD` and `XOAM_CLIENT_SECRET` constants to keep secrets in `wp-config.php`.
- Debug log that hides credentials and message content.
- Test email tool, Help tab and suggested privacy policy text.
- Clean uninstall that removes all plugin data.

[Unreleased]: https://github.com/M-Awais-irfan/xoauth-mailer/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/M-Awais-irfan/xoauth-mailer/releases/tag/v1.0.0
