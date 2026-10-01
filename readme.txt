=== XOAuth Mailer – SMTP for Google Workspace ===
Contributors: awaisirfan
Tags: smtp, google workspace, gmail, email, oauth2
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 2.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send WordPress email through Google Workspace with Google's XOAUTH2 SMTP mechanism or an App Password. No extra libraries.

== Description ==

**XOAuth Mailer – SMTP for Google Workspace** replaces WordPress's default PHP mail() with a reliable Google Workspace SMTP connection.

It is built for one job and kept deliberately small: it speaks Google's XOAUTH2 SMTP mechanism directly on top of the PHPMailer that ships with WordPress, so there is no Composer dependency, no Google API client and no OAuth library bundled — just a few small classes you can read in one sitting.

**Features:**

* Two authentication methods — App Password (quick setup) or OAuth2 (recommended for production)
* Full OAuth2 flow with automatic token refresh — your Google password is never stored
* Conflict-free OAuth callback using WordPress REST API — no interference from other plugins (Constant Contact, Jetpack, etc.)
* Live debug log with PHPMailer output
* Test email sender with one click
* Clean uninstall — removes all data on deletion
* Extensible architecture — built to support additional providers in future releases

**Why OAuth2 over App Password?**

App Passwords are simpler to set up, but the App Password itself is stored in your database and works until you delete it in your Google account. With OAuth2 the plugin stores your OAuth Client ID and Secret plus a refresh token instead: your Google password is never used, access tokens expire after about an hour, and access can be revoked at any time from your Google account or from the plugin — more secure for production sites.

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
2. Go to myaccount.google.com/apppasswords → create a password named "WordPress"
3. In XOAuth Mailer → Settings: enter your email, paste the app password, save
4. Send a test email

**OAuth2 (Recommended):**

1. Go to console.cloud.google.com → enable Gmail API
2. Create an OAuth 2.0 Client ID (Web application type)
3. Copy the Redirect URI from XOAuth Mailer → OAuth2 tab into Google Cloud → Authorized redirect URIs
4. Paste Client ID and Secret into XOAuth Mailer → Settings → save
5. Add your email as a test user in Google Cloud → Audience if in testing mode
6. Go to OAuth2 tab → click Connect Google Account

== Frequently Asked Questions ==

= Do I need to install any other plugin or library? =
No. Everything is self-contained. No Composer, no extra packages.

= Can I use this on multiple sites with the same Google Workspace account? =
Yes. Use the same Client ID and Client Secret on all sites. Add each site's Redirect URI to Google Cloud. Connect each site separately.

= Why does the OAuth2 tab show "Not Connected" after approving? =
Another plugin (e.g. Constant Contact) may be intercepting the callback. This plugin uses a REST API endpoint (/wp-json/xoauth-mailer/v1/oauth-callback) which cannot be intercepted. If you upgraded from version 1.x, update the redirect URI in Google Cloud to the one shown in the OAuth2 tab, then reconnect.

= Will my settings be deleted if I deactivate the plugin? =
No. Settings are only deleted when you delete the plugin. Deactivation preserves everything.

= Is this compatible with WordPress Multisite? =
Single-site tested and supported. Multisite support is planned.

== Screenshots ==

1. Settings — sender identity, SMTP server and authentication method
2. OAuth2 Setup — redirect URI to copy, connection status and step-by-step Google Cloud guide
3. Test Email — send a real email through your configured account
4. Debug Log — SMTP conversation with credentials hidden
5. Help — choosing an auth method and fixing common issues

== Changelog ==

= 2.2.0 =
* Changed: plugin renamed to "XOAuth Mailer – SMTP for Google Workspace" (slug: xoauth-mailer); all code, options and hooks now use the "xoam" prefix
* Changed: OAuth redirect URI is now /wp-json/xoauth-mailer/v1/oauth-callback
* Added: settings, Google connection and debug log from earlier versions are migrated automatically

= 2.1.0 =
* Security: OAuth2 access token is no longer written to the debug log
* Security: saved App Password and Client Secret are no longer printed into the settings page source
* Security: App Password and Client Secret can be defined in wp-config.php (XOAM_APP_PASSWORD, XOAM_CLIENT_SECRET)
* Security: secret options are no longer autoloaded
* Security: OAuth2 credential is no longer stored in a database transient during sending
* Security: optional token revocation at Google when disconnecting
* Security: hardened OAuth callback state handling
* Privacy: the debug log no longer stores email content (message headers and body)
* Fixed: From Name is now applied when From Email is left empty
* Fixed: setup steps now add the test user before connecting (Google blocks non-test users in Testing mode)
* Added: clear "saved" indicator under the App Password and Client Secret fields
* Added: upgrade routine, so data changes apply on plugin update without reactivating
* Changed: Auth Method options are shown one per line
* Fixed: escaping, input unslashing and validation flagged by Plugin Check
* Added: privacy policy suggested text
* Added: all interface strings are now translatable
* Changed: Connect button uses a neutral icon

= 2.0.0 =
* Complete rewrite with proper file structure and class architecture
* OAuth2 callback moved to REST API endpoint — eliminates all plugin conflicts
* Separated into includes/, admin/, assets/ for WordPress directory standards
* Added uninstall.php for clean data removal
* Added admin.css and admin.js as proper enqueued assets
* Added text domain for translation readiness

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 2.2.0 =
Plugin renamed. Your settings and Google connection carry over automatically. Before reconnecting, add the new redirect URI shown in the OAuth2 tab to your Google Cloud OAuth client.

= 2.1.0 =
Security release. Clear the Debug Log after upgrading. The App Password and Client Secret fields now appear empty — leave them blank to keep the saved values.

= 2.0.0 =
Major rewrite. After upgrading: go to Settings → Permalinks → Save to flush rewrite rules, then reconnect your Google account in the OAuth2 tab.
