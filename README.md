# XOAuth Mailer for Google Workspace

A WordPress plugin that sends all site email through Google Workspace SMTP, using Google's XOAUTH2 mechanism (OAuth2) or an App Password.

It is built on the PHPMailer that ships with WordPress. There are no Composer packages, no Google API client and no OAuth library to install.

## Features

- OAuth2 (XOAUTH2) or App Password authentication
- Automatic access token refresh
- OAuth callback on its own REST route: `/wp-json/xoauth-mailer/v1/oauth-callback`
- Saved secrets are never printed in the page source, and can be defined in `wp-config.php` instead
- Debug log that hides credentials and message content
- Test email tool and a clean uninstall

## Requirements

- WordPress 6.0 or later
- PHP 8.0 or later
- A Google Workspace account

## Installation

1. Download the latest release zip, then go to **Plugins > Add New > Upload Plugin** in WordPress.
2. Activate **XOAuth Mailer for Google Workspace** and open **XOAuth Mailer** in the admin menu.
3. Follow the steps on the **OAuth2 Setup** tab, or use an App Password on the **Settings** tab.

For OAuth2, set the app's user type to **Internal** under Google Auth Platform > Audience. External apps in Testing status lose their connection after 7 days.

### Keeping secrets out of the database

```php
define( 'XOAM_APP_PASSWORD', 'your-app-password' );
define( 'XOAM_CLIENT_SECRET', 'your-client-secret' );
```

When a constant is defined, the matching field on the Settings tab is replaced by a note and nothing is stored in the database.

## How it works

1. `phpmailer_init`, priority 10: `XOAM_Mailer::configure()` sets the host, port, encryption (derived from the port) and sender. For OAuth2 it refreshes the access token when it has less than 60 seconds left and builds the XOAUTH2 string in memory.
2. `phpmailer_init`, priority 20: `XOAM_Mailer::inject_xoauth2()` replaces PHPMailer's SMTP object with `XOAM_SMTP`, which authenticates with `AUTH XOAUTH2 <base64>`.
3. Connecting: `XOAM_OAuth::get_auth_url()` stores a single-use state value for 10 minutes. The REST callback checks it, confirms the user who started the flow still has `manage_options`, and exchanges the code for tokens.

## Project structure

```
xoauth-mailer/
  xoauth-mailer.php        Plugin header, constants, autoloader
  uninstall.php            Removes all plugin data on delete
  readme.txt               WordPress.org readme
  includes/
    class-xoam-core.php      Boot, upgrade routine, activation
    class-xoam-settings.php  Defaults, sanitizing, wp-config constants
    class-xoam-mailer.php    phpmailer_init configuration
    class-xoam-smtp.php      PHPMailer SMTP subclass for XOAUTH2
    class-xoam-oauth.php     OAuth flow, REST callback, token refresh
    class-xoam-logger.php    Debug log (last 100 entries)
  admin/
    class-xoam-admin.php     Menu, settings page, form handlers
    views/                   One template per tab
  assets/                  Admin CSS and JS
```

## Development

- Follows the WordPress Coding Standards (PHPCS with WPCS).
- Prefix: `XOAM_` for classes and constants, `xoam_` for options and hooks. Text domain: `xoauth-mailer`.

## License

GPLv2 or later. See [the GPL v2](https://www.gnu.org/licenses/gpl-2.0.html).
