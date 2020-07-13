=== WordPress Simple SMTP ===
Contributors: soupbowl
Tags: mail,email,smtp,dispatch,sender
Requires at least: 4.9
Tested up to: 5.4
Requires PHP: 7.0
Stable tag: trunk
License: MIT

Adds a simple mail configuration panel into your WordPress installation. Supports logging and config variables.

== Description ==
Adds a simple, no-fuss SMTP settings to your WordPress installation that lets you define custom settings, which is especially useful for hosts with no control over the php `mail` functionality.

## Environment and constant overriding (optional)
This plugin will prefer environmental and constant-stored values over the plugin-saved equivalent settings, making it easier to use this plugin via deployment.

These can be either stored in your systems env setup, or in wp-config.php as `define( 'SEE_BELOW', 'your_value_here' );`.

### Accepted Parameters
* `SMTP_HOST` (string) Mail server hostname.
* `SMTP_PORT` (integer) Port address (usually 25, 465 or 587).
* `SMTP_AUTH` (integer, 1 or 0) Pass below credentials to your mail server.
* `SMTP_USER` (string) The mail username for this account.
* `SMTP_PASS` (string) The password for the mailer account.
* `SMTP_LOG` (boolean) Controls the logging capability and visibility.

`SMTP_PASS` is stored as **plaintext**! Where you wish to store it depends on your configuration, but as a minimum it is recommended to store at least `SMTP_PASS` in your wp-config.php file (with the correct file permissions set).

== Frequently Asked Questions ==
= One or more of the settings are greyed out =
This plugin supports being overridden by DEFINE, so please check to see that you are not setting a define for a WP Simple SMTP option. These are most commonly stored in the wp-config.php file.

= How is the SMTP password stored? = 
The SMTP password is saved into the database *plaintext*. The more recommended way of storing the password is to define SMTP_PASS in your wp-config.php file, which should already be locked and inaccessible from the front-end.

== Changelog ==
= 0.3.3 =
* Independent log tables deprecated for CPT.
= 0.3.2 =
* Changed display format of email log.
* Limit resent emails to hourly.
= 0.3.1 =
* Table is created or deleted upon plugin state change.
= 0.3 =
* Changes to test emails.
* Log view changed depending on header.
= 0.2 =
* SMTP error logging.
* View and resend emails.
* Test email settings.
= 0.1 =
* SMTP configuration handling (overrides `mail()`).
* Optional SMTP logging (basic functionality).
