# XOAuth Mailer for Google Workspace

[![CI](https://github.com/M-Awais-irfan/xoauth-mailer/actions/workflows/ci.yml/badge.svg)](https://github.com/M-Awais-irfan/xoauth-mailer/actions/workflows/ci.yml)
[![Latest release](https://img.shields.io/github/v/release/M-Awais-irfan/xoauth-mailer)](https://github.com/M-Awais-irfan/xoauth-mailer/releases/latest)
![WordPress 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-21759b)
![PHP 8.0+](https://img.shields.io/badge/PHP-8.0%2B-777bb4)
[![License: GPL v2 or later](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)](LICENSE)

A WordPress plugin that sends all site email through Google Workspace SMTP, signing in with Google's XOAUTH2 mechanism (OAuth2) or an App Password.

It runs on the PHPMailer that ships with WordPress. There is no Composer package to install on your site, no Google API client and no OAuth library.

The plugin has been submitted to the WordPress.org plugin directory and is under review.

## Features

- **Two sign-in methods:** OAuth2 (recommended) or an App Password
- **Automatic token refresh:** access tokens are renewed shortly before they expire
- **Clear connection status:** a revoked or expired Google grant shows as "Not Connected" instead of failing silently
- **Secrets stay hidden:** saved passwords are never printed in the page source, and can live in `wp-config.php` instead of the database
- **Privacy-aware debug log:** shows the SMTP conversation with credentials and message content removed
- **Conflict-free OAuth callback:** uses its own REST route, so other plugins' OAuth handlers can't intercept it
- **Test email tool, Help tab and clean uninstall**

## Requirements

- WordPress 6.0 or later
- PHP 8.0 or later
- A Google Workspace account

## Installation

1. Download `xoauth-mailer.zip` from the [latest release](https://github.com/M-Awais-irfan/xoauth-mailer/releases/latest). Use that file, not the "Source code" archives.
2. In WordPress, go to **Plugins > Add New > Upload Plugin**, choose the zip and activate **XOAuth Mailer for Google Workspace**.
3. Open **XOAuth Mailer** in the admin menu.

## Setup

### OAuth2 (recommended)

1. In the [Google Cloud Console](https://console.cloud.google.com/), select or create a project and enable the **Gmail API**.
2. Under **Google Auth Platform > Audience**, set the user type to **Internal**.
3. Create an **OAuth 2.0 Client ID** of type **Web application**.
4. Add the redirect URI shown on the plugin's **OAuth2 Setup** tab as an authorized redirect URI. It looks like `https://example.com/wp-json/xoauth-mailer/v1/oauth-callback`.
5. Paste the Client ID and Client Secret into the plugin's **Settings** tab and save.
6. On the **OAuth2 Setup** tab, click **Connect Google Account**, then send a test email from the **Test Email** tab.

Why Internal? Google expires refresh tokens after 7 days for External apps that are still in Testing status, which would stop your email every week.

### App Password

1. Turn on 2-Step Verification for the Google account.
2. Create an App Password at [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords).
3. In the plugin's **Settings** tab, choose **App Password**, enter the account email and the App Password, and save.

### Keeping secrets out of the database

```php
define( 'XOAM_APP_PASSWORD', 'your-app-password' );
define( 'XOAM_CLIENT_SECRET', 'your-client-secret' );
```

Add these to `wp-config.php`. The matching field on the Settings tab is then replaced by a note, and nothing is stored in the database.

## How it works

1. **`phpmailer_init`, priority 10:** `XOAM_Mailer::configure()` sets the host, port, encryption (derived from the port) and sender. For OAuth2 it refreshes the access token when less than 60 seconds remain and builds the XOAUTH2 string in memory.
2. **`phpmailer_init`, priority 20:** `XOAM_Mailer::inject_xoauth2()` replaces PHPMailer's SMTP object with `XOAM_SMTP`, which authenticates with `AUTH XOAUTH2 <base64>`.
3. **Connecting:** `XOAM_OAuth::get_auth_url()` stores a single-use state value for 10 minutes. The REST callback checks it, confirms the user who started the flow still has `manage_options`, and exchanges the code for tokens.

## Privacy and external services

The plugin talks only to Google: `smtp.gmail.com` to send mail, and `accounts.google.com` and `oauth2.googleapis.com` for OAuth2. It sends no data anywhere else and has no tracking. The full list of what is sent and when is in the **External services** section of [readme.txt](readme.txt).

## Development

```
composer install
composer lint   # coding standards: security, i18n, prefixes, PHP compatibility
composer test   # tests in tests/
```

[GitHub Actions](https://github.com/M-Awais-irfan/xoauth-mailer/actions) runs a PHP syntax check on PHP 8.0 to 8.4, the coding standards and the tests on every push and pull request.

To build a release zip that contains only the plugin files:

```
git archive --format=zip --prefix=xoauth-mailer/ -o xoauth-mailer.zip v1.0.0
```

### Project structure

```
xoauth-mailer.php            Plugin header, constants, autoloader
uninstall.php                Removes all plugin data on delete
readme.txt                   WordPress.org readme
includes/
  class-xoam-core.php        Boot, upgrade routine, activation
  class-xoam-settings.php    Defaults, sanitizing, wp-config constants
  class-xoam-mailer.php      phpmailer_init configuration
  class-xoam-smtp.php        PHPMailer SMTP subclass for XOAUTH2
  class-xoam-oauth.php       OAuth flow, REST callback, token refresh
  class-xoam-logger.php      Debug log (last 100 entries)
admin/
  class-xoam-admin.php       Menu, settings page, form handlers
  views/                     One template per tab
assets/                      Admin CSS and JS
tests/                       Tests run by `composer test`
```

## Contributing and security

Bug reports and pull requests are welcome; see [CONTRIBUTING.md](CONTRIBUTING.md). Please report security issues privately as described in [SECURITY.md](SECURITY.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

[GPL v2 or later](LICENSE).
