# XOAuth Mailer – SMTP for Google Workspace — Developer Documentation

> **Version:** 2.2.0 | **Author:** Awais Irfan | **PHP:** 8.0+ | **WP:** 6.0+

---

## Table of Contents

1. [Project Structure](#project-structure)
2. [Architecture Overview](#architecture-overview)
3. [Class Reference](#class-reference)
4. [How OAuth2 Works](#how-oauth2-works)
5. [How XOAUTH2 SMTP Works](#how-xoauth2-smtp-works)
6. [Adding a New Mail Provider](#adding-a-new-mail-provider)
7. [Hooks & Filters](#hooks--filters)
8. [Coding Standards](#coding-standards)
9. [Debugging](#debugging)

---

## Project Structure

```
xoauth-mailer/
├── xoauth-mailer.php          # Bootstrap: constants, autoloader, activation hooks
├── uninstall.php                # Deletes all plugin data on uninstall
├── readme.txt                   # WordPress.org user-facing readme
├── README.md                    # This file — developer documentation
│
├── includes/
│   ├── class-xoam-core.php      # Boots plugin, loads dependencies, registers hooks
│   ├── class-xoam-settings.php  # Settings CRUD, defaults, sanitization
│   ├── class-xoam-logger.php    # Debug log (DB-backed, max 100 entries)
│   ├── class-xoam-mailer.php    # phpmailer_init hook, XOAUTH2 injection
│   ├── class-xoam-oauth.php     # OAuth2 flow, REST callback, token refresh
│   └── class-xoam-smtp.php  # PHPMailer SMTP subclass for XOAUTH2
│
├── admin/
│   ├── class-xoam-admin.php     # Admin menu, settings registration, form actions
│   └── views/
│       ├── tab-settings.php         # Settings form
│       ├── tab-oauth.php            # OAuth2 setup + connect button
│       ├── tab-test.php             # Test email form
│       ├── tab-debug.php            # Debug log viewer
│       └── tab-help.php             # Help & troubleshooting
│
└── assets/
    ├── admin.css                # Admin UI styles
    └── admin.js                 # Auth toggle + clipboard copy
```

---

## Architecture Overview

```
WordPress boot
     │
plugins_loaded
     │
XOAM_Core::init()
     ├── load_dependencies()        ← Force-loads PHPMailer SMTP files, then XOAM_SMTP
     └── register_hooks()
           ├── XOAM_Mailer::register()     ← phpmailer_init (priority 10 + 20)
           ├── XOAM_OAuth::register()      ← REST API endpoint
           ├── XOAM_Admin::register()      ← admin_menu, admin_init, admin_post_*
           └── XOAM_Logger::log_mail_failure() ← wp_mail_failed

wp_mail() called
     │
phpmailer_init (priority 10) — XOAM_Mailer::configure()
     ├── Sets host, port, encryption, from address
     ├── App Password → sets $phpmailer->Password directly
     └── OAuth2 → builds XOAUTH2 base64 string, keeps it in a static property, sets SMTPAuth=false
     │
phpmailer_init (priority 20) — XOAM_Mailer::inject_xoauth2()
     └── OAuth2 only → swaps $phpmailer->smtp with XOAM_SMTP instance
                        sets xoauth2_token on instance, re-enables SMTPAuth
     │
SMTP connection
     └── XOAM_SMTP::authenticate()
           └── Sends: AUTH XOAUTH2 <base64string>
                 Google responds: 235 Authentication succeeded
```

---

## Class Reference

### `XOAM_Core`
Singleton. Entry point for the entire plugin.

| Method | Description |
|--------|-------------|
| `init()` | Called on `plugins_loaded`. Creates singleton, boots plugin. |
| `load_dependencies()` | Loads PHPMailer files and XOAUTH2 class before hooks fire. |
| `register_hooks()` | Wires up Mailer, OAuth, Admin, and Logger. |
| `activate()` | Runs on activation — stores timestamp, turns off autoload for secret options, logs event. |
| `deactivate()` | Runs on deactivation — logs event (settings are kept). |

---

### `XOAM_Settings`
Static class. Single source of truth for all settings.

| Method | Description |
|--------|-------------|
| `get(): array` | Returns all settings merged with defaults. |
| `get_one(key, fallback): mixed` | Returns a single setting value. |
| `sanitize(input): array` | WordPress `sanitize_callback` for `register_setting()`. |

**Adding a new setting:**
1. Add key + default to `$defaults`
2. Add sanitization rule to `sanitize()`
3. Add field to `admin/views/tab-settings.php`

---

### `XOAM_Logger`
Static class. Writes to `wp_options` (key: `xoam_debug_log`).

| Method | Description |
|--------|-------------|
| `log(message, level)` | Writes entry. Levels: INFO, WARN, ERROR, DEBUG. ERRORs always logged. |
| `get_entries(): array` | Returns all entries, newest first. |
| `clear()` | Deletes all log entries. |
| `log_mail_failure(WP_Error)` | Hook callback for `wp_mail_failed`. |

---

### `XOAM_Mailer`
Static class. Handles all `phpmailer_init` configuration.

| Method | Description |
|--------|-------------|
| `register()` | Adds `phpmailer_init` hooks at priority 10 and 20. |
| `configure(PHPMailer)` | Priority 10 — sets SMTP server, auth method. |
| `inject_xoauth2(PHPMailer)` | Priority 20 — swaps SMTP instance for XOAUTH2 subclass. |
| `configure_app_password()` | Private — sets `$phpmailer->Password`. |
| `configure_oauth2()` | Private — builds XOAUTH2 string, stores in transient. |

---

### `XOAM_OAuth`
Static class. Manages the full OAuth2 lifecycle.

| Method | Description |
|--------|-------------|
| `register()` | Registers REST API route on `rest_api_init`. |
| `register_rest_route()` | Creates `/wp-json/xoauth-mailer/v1/oauth-callback` endpoint. |
| `get_redirect_uri(): string` | Returns the REST endpoint URL for Google Cloud Console. |
| `get_auth_url(): string` | Builds Google authorization URL with state transient. |
| `handle_callback(WP_REST_Request)` | Exchanges code for token, stores in DB. |
| `refresh_token(token, settings): array` | Refreshes expired access token. |
| `disconnect()` | Deletes stored token. |
| `is_connected(): bool` | Returns true if access token exists. |
| `get_token(): array` | Returns raw token data. |

---

### `XOAM_SMTP`
Extends `PHPMailer\PHPMailer\SMTP`.

| Property/Method | Description |
|-----------------|-------------|
| `$xoauth2_token` | Base64 XOAUTH2 credential string. Set by `XOAM_Mailer::inject_xoauth2()`. |
| `authenticate(...)` | Overrides parent to send `AUTH XOAUTH2 <token>`. Falls back to parent if token empty. |

**Important:** Method signature must exactly match parent — no type hints.

---

## How OAuth2 Works

```
Admin clicks "Connect Google Account"
        │
XOAM_OAuth::get_auth_url()
        ├── Generates random $state → transient xoam_oauth_state_{state} = user ID (10 min TTL)
        └── Redirects to accounts.google.com/o/oauth2/v2/auth
                │
        User approves permissions
                │
Google redirects to:
https://yoursite.com/wp-json/xoauth-mailer/v1/oauth-callback?code=XXX&state=YYY
                │
XOAM_OAuth::handle_callback()
        ├── Looks up the state transient (CSRF protection; single-use)
        ├── Checks the initiating user still has manage_options
        │   (no login session here — Google's redirect carries no WP REST nonce)
        ├── POSTs to oauth2.googleapis.com/token with code + client credentials
        ├── Stores { access_token, refresh_token, expires_at } in wp_options
        └── Redirects to admin OAuth2 tab with success notice
```

**Why REST API for the callback?**
Other OAuth plugins (Constant Contact, Jetpack, etc.) hook into `admin_init` and scan for `?code=` in any admin URL. The REST API namespace `/wp-json/xoauth-mailer/v1/` is completely isolated — no other plugin's hooks run on it.

---

## How XOAUTH2 SMTP Works

Google's SMTP server does not accept OAuth access tokens via `AUTH LOGIN`.
It requires the `XOAUTH2` mechanism:

```
CLIENT → SERVER: AUTH XOAUTH2 <base64string>
```

Where `base64string` is:
```
base64( "user=<email>\x01auth=Bearer <access_token>\x01\x01" )
```

WordPress's bundled PHPMailer does not support this natively (it would require
the `league/oauth2-google` library). Instead, we subclass `PHPMailer\PHPMailer\SMTP`
and override `authenticate()` to send this command directly.

**Two-hook approach (why):**
We use two `phpmailer_init` hooks:
- Priority 10: Configure SMTP settings, build XOAUTH2 string, keep it in a static property (same request — never written to the DB)
- Priority 20: Swap in `XOAM_SMTP` instance and attach token

This separation ensures the SMTP subclass is only injected after all
configuration is complete, and only when OAuth2 is the selected method.

---

## Adding a New Mail Provider

> Example: Adding Outlook / Microsoft 365 SMTP

### Step 1 — Add settings defaults

In `includes/class-xoam-settings.php`, add to `$defaults`:

```php
'provider'             => 'google',   // 'google' | 'outlook' | 'sendgrid'
'outlook_client_id'    => '',
'outlook_client_secret'=> '',
'outlook_tenant_id'    => '',
```

### Step 2 — Add provider config to Mailer

In `includes/class-xoam-mailer.php`, update `configure()`:

```php
// Change SMTP host/port based on provider
if ( $s['provider'] === 'outlook' ) {
    $phpmailer->Host = 'smtp.office365.com';
    $phpmailer->Port = 587;
}

// Route to provider-specific auth
match ( $s['provider'] ) {
    'outlook' => self::configure_outlook_oauth( $phpmailer, $s ),
    default   => $s['auth_method'] === 'oauth2'
        ? self::configure_oauth2( $phpmailer, $s )
        : self::configure_app_password( $phpmailer, $s ),
};
```

Then add the method:

```php
private static function configure_outlook_oauth(
    PHPMailer\PHPMailer\PHPMailer $phpmailer,
    array $s
): void {
    // Microsoft uses different OAuth endpoints + scopes
    // Build XOAUTH2 string same way, different token source
    XOAM_Logger::log( 'Auth method: Outlook OAuth2.' );
    // ... your implementation
}
```

### Step 3 — Add OAuth class for provider (optional)

If the provider needs its own OAuth flow, create:
`includes/class-xoam-outlook-oauth.php`

Mirror the structure of `XOAM_OAuth` but with Microsoft endpoints:
- Auth URL: `https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize`
- Token URL: `https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token`
- Scope: `https://outlook.office365.com/SMTP.Send offline_access`

Register its REST route in `XOAM_Core::register_hooks()`.

### Step 4 — Add admin UI

Add settings fields to `admin/views/tab-settings.php` under a new provider section.
Add a new tab view `admin/views/tab-outlook-oauth.php` if needed.
Add the tab key to `$tabs` array in `XOAM_Admin::render_page()`.

### Step 5 — Update autoloader

Add the new class to the map in `xoauth-mailer.php`:

```php
'XOAM_Outlook_OAuth' => 'includes/class-xoam-outlook-oauth.php',
```

---

## Hooks & Filters

The plugin does not currently expose public filters but is designed for it.
Planned filter hooks for next release:

```php
// Modify SMTP settings before PHPMailer is configured
apply_filters( 'xoam_mailer_settings', $settings );

// Modify the XOAUTH2 string before it is sent
apply_filters( 'xoam_xoauth2_string', $xoauth2, $username );

// Modify the OAuth2 scopes requested from Google
apply_filters( 'xoam_oauth_scopes', 'https://mail.google.com/' );
```

To add these, wrap the relevant values in `apply_filters()` calls inside
`XOAM_Mailer` and `XOAM_OAuth`.

---

## Coding Standards

This plugin follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).

- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`
- All input sanitized before use or storage
- Nonces on all admin form submissions (`wp_nonce_field` + `check_admin_referer`)
- Text domain `xoauth-mailer` on all translatable strings
- No inline SQL — use `$wpdb` prepared statements if DB queries needed
- `defined( 'ABSPATH' ) || exit;` at top of every file

**To run PHPCS:**
```bash
composer require --dev wp-coding-standards/wpcs
vendor/bin/phpcs --standard=WordPress includes/ admin/ xoauth-mailer.php
```

---

## Debugging

### Enable debug log
Go to **XOAuth Mailer → Settings → Enable Debug Log → Save**.

The Debug Log tab shows:
- Full PHPMailer SMTP conversation (CLIENT ↔ SERVER)
- Token exchange HTTP status
- XOAUTH2 injection confirmation
- Any ERROR level entries (always logged regardless of debug toggle)

### Common log entries to look for

| Entry | Meaning |
|-------|---------|
| `XOAM_SMTP injected into PHPMailer` | XOAUTH2 class successfully swapped in |
| `Sending AUTH XOAUTH2 command` | About to authenticate with Google |
| `235 Authentication succeeded` | OAuth2 auth working |
| `XOAUTH2 credential missing` | Token build failed — check configure_oauth2() |
| `Token exchange failed` | Google rejected the code — check Client ID/Secret and redirect URI |

### Direct DB inspection
```sql
-- Check stored token
SELECT option_value FROM wp_options WHERE option_name = 'xoam_oauth_token';

-- Check settings
SELECT option_value FROM wp_options WHERE option_name = 'xoam_settings';

-- Check log
SELECT option_value FROM wp_options WHERE option_name = 'xoam_debug_log';
```
