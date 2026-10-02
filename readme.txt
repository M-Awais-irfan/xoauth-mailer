=== XOAuth Mailer for Google Workspace ===
Contributors: awaisirfan
Tags: smtp, google workspace, gmail, email, oauth2
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send WordPress email through Google Workspace with Google's XOAUTH2 SMTP mechanism or an App Password. No extra libraries.

== Description ==

**XOAuth Mailer for Google Workspace** replaces WordPress's default PHP mail() with a reliable Google Workspace SMTP connection.

It is built for one job and kept deliberately small: it speaks Google's XOAUTH2 SMTP mechanism directly on top of the PHPMailer that ships with WordPress, so there is no Composer dependency, no Google API client and no OAuth library bundled.

**Features:**

* Two authentication methods: App Password (quick setup) or OAuth2 (recommended for production)
* Full OAuth2 flow with automatic token refresh; your Google password is never stored
* OAuth callback on its own REST API route, so other plugins' OAuth handlers can't intercept it
* Live debug log with PHPMailer output
* Test email sender with one click
* Clean uninstall: removes all data on deletion

**Why OAuth2 over App Password?**

App Passwords are simpler to set up, but the App Password itself is stored in your database and works until you delete it in your Google account. With OAuth2 the plugin stores your OAuth Client ID and Secret plus a refresh token instead: your Google password is never used, access tokens expire after about an hour, and access can be revoked at any time from your Google account or from the plugin. That makes it the better choice for production sites.

You can keep the App Password or Client Secret out of the database entirely by defining them in `wp-config.php`:

`define( 'XOAM_APP_PASSWORD', 'your-app-password' );`
`define( 'XOAM_CLIENT_SECRET', 'your-client-secret' );`

== External services ==

This plugin connects to Google services to deliver your site's email. Nothing is sent until you configure the plugin.

**Google SMTP server (smtp.gmail.com)**
Used for every email WordPress sends once the plugin is configured. Sent: the full message (sender, recipients, subject, body, attachments), your Google account email address, and either your App Password or an OAuth2 access token.

**Google OAuth 2.0 (accounts.google.com, oauth2.googleapis.com)**
Used only when the OAuth2 method is selected.

* When you click "Connect Google Account", your browser is sent to accounts.google.com with your Client ID and this site's redirect URI.
* After you approve, the plugin sends the authorization code, Client ID and Client Secret to oauth2.googleapis.com to obtain tokens.
* When the access token expires (about every hour while emails are being sent), the plugin sends the refresh token, Client ID and Client Secret to oauth2.googleapis.com to get a new one.
* When you disconnect with "Also revoke access at Google" checked, the refresh token is sent to oauth2.googleapis.com/revoke.

These services are provided by Google: [Terms of Service](https://policies.google.com/terms), [Privacy Policy](https://policies.google.com/privacy), [Google API Services User Data Policy](https://developers.google.com/terms/api-services-user-data-policy).

== Installation ==

1. Upload the `xoauth-mailer` folder to `/wp-content/plugins/`
2. Activate the plugin from the Plugins menu
3. Go to **XOAuth Mailer** in the admin sidebar

**App Password (Quick Setup):**

1. Enable 2-Step Verification on your Google account
2. Go to myaccount.google.com/apppasswords > create a password named "WordPress"
3. In XOAuth Mailer > Settings: enter your email, paste the app password, save
4. Send a test email

**OAuth2 (Recommended):**

1. Go to console.cloud.google.com > enable Gmail API
2. Under Google Auth Platform > Audience, set the user type to Internal
3. Create an OAuth 2.0 Client ID (Web application type)
4. Copy the Redirect URI from XOAuth Mailer > OAuth2 tab into Google Cloud > Authorized redirect URIs
5. Paste Client ID and Secret into XOAuth Mailer > Settings > save
6. Go to OAuth2 tab > click Connect Google Account

== Frequently Asked Questions ==

= Do I need to install any other plugin or library? =
No. Everything is self-contained. No Composer, no extra packages.

= Can I use this on multiple sites with the same Google Workspace account? =
Yes. Use the same Client ID and Client Secret on all sites. Add each site's Redirect URI to Google Cloud. Connect each site separately.

= Why does the OAuth2 tab show "Not Connected" after approving? =
Another plugin may be intercepting the callback. This plugin uses its own REST API endpoint (/wp-json/xoauth-mailer/v1/oauth-callback) to avoid that.

= Why does sending stop after about 7 days? =
Your OAuth app is set to External with the Testing publishing status, and Google expires refresh tokens for those apps after 7 days. Set the user type to Internal under Google Auth Platform > Audience, then reconnect in the OAuth2 tab.

= Will my settings be deleted if I deactivate the plugin? =
No. Settings are only deleted when you delete the plugin. Deactivation preserves everything.

= Is this compatible with WordPress Multisite? =
Single-site tested and supported. Multisite support is planned.

== Screenshots ==

1. Settings: sender identity, SMTP server and authentication method
2. OAuth2 Setup: redirect URI to copy, connection status and step-by-step Google Cloud guide
3. Test Email: send a real email through your configured account
4. Debug Log: SMTP conversation with credentials hidden
5. Help: choosing an auth method and fixing common issues

== Changelog ==

= 1.0.0 =
* Initial release.
