=== Simple SMTP ===
Contributors: soupbowl
Tags: mail,email,smtp,dispatch,sender
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: {{VERSION_NUMBER}}
License: MIT

Adds a simple mail configuration panel into your WordPress installation. Supports temporary logging and config variables.

== Description ==
Adds a simple, no-fuss SMTP settings to your WordPress installation that lets you define custom settings, which is especially useful for hosts with no control over the php `mail` functionality.

If logging is enabled, a new segment in the settings panel will show up with a 30-day overview of recent emails, and will automatically prune older logs. Please see the FAQ if you want a more permanent solution.

For more information, please see the [project wiki on GitHub](https://github.com/soup-bowl/wp-simple-smtp/wiki).

## Environment and constant overriding (optional)

This plugin will prefer environmental and constant-stored values over the plugin-saved equivalent settings, making it easier to use this plugin via deployment.

These can be either stored in your systems env setup, or in wp-config.php as `define( 'SEE_BELOW', 'your_value_here' );`.

### Accepted Parameters

* `SMTP_HOST` (string) Mail server hostname.
* `SMTP_PORT` (integer) Port address (usually 25, 465 or 587).
* `SMTP_AUTH` (integer, 1 or 0) Pass below credentials to your mail server.
* `SMTP_USER` (string) The mail username for this account.
* `SMTP_PASS` (string) The password for the mailer account.
* `SMTP_FROM` (string) Enforce all emails come from this email address.
* `SMTP_FROMNAME` (string) Enforce all emails to have a certain email name.
* `SMTP_SEC` (string) Use a particular email security method (accepts 'def' (default), 'ssl', 'tls' and 'off').
* `SMTP_NOVERIFYSSL` (boolean) Disable validation of the SMTP server certificate (not recommended).
* `SMTP_LOG` (boolean) Controls the logging capability and visibility.
* `SMTP_DISABLE` (boolean) Disables the mailer. They will still be logged if enabled, but won't send out.

It is recommended to store at least `SMTP_PASS` in your wp-config.php file (with the correct file permissions set). If the openssl extension is available, the plugin will attempt to encrypt the password in the database.

== Frequently Asked Questions ==
= How do I fix SMTP errors? =
This plugin works by instructing **PHPMailer** - the mail library WordPress have chosen - to use SMTP mode, and adds in the settings you choose. 9 times out of 10, the error messages you receive are configuration errors. PHPMailer provides a good guide to help you figure out these problems.

[Troubleshooting - PHPMailer](https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting).

The one instance where an SMTP error can be caused by this plugin is if the SMTP password is stored in the database when the **secret keys** have been regenerated. You will need to re-save the password to refresh the encryption keys.

You can always get assistance from your host and/or SMTP service provider.

= Plugin compatibility =
When using the **logging** functionality, the plugin will store the logged emails in the posts table, as an invisible post type. Normally this should work completely fine, but if you have a plugin that scans custom post types and sends an email about them, there's a chance the third-party plugin might get stuck in a loop. For plugins like this, it is best to disable the functionality on the logging post type (sbss_email_log).

The following plugins have had reported issues:
* [Sucuri Security](https://github.com/soup-bowl/wp-simple-smtp/issues/115).

= One or more of the settings are greyed out =
This plugin supports being overridden by DEFINE, so please check to see that you are not setting a define for a WP Simple SMTP option. These are most commonly stored in the wp-config.php file.

The over-ride hierachy is as follows, with top being the most important.

* Environmental variable.
* Constant variable (wp-config define).
* Multisite network settings.
* Locally-configured settings.

= How do I stop the logs from automatically purging? =
The logs by default will auto-prune to avoid keeping sensitive details in logs and contributing to database bloat. But if you wish to keep the logs more permanently, then register the following hook (typically in your theme functions.php) to disable the auto-pruning functionality:

	add_filter( 'simple_smtp_disable_log_prune', '__return_true' ); 

= How is the SMTP password stored? = 
If openssl is available to PHP, then the password will be **encrypted** ([not hashed](https://stackoverflow.com/a/4948393)) when stored in the database. If unavailable, the SMTP password will be saved into the database as **plaintext**. The more recommended way of storing the password is to define SMTP_PASS in your wp-config.php file, which should already be locked and inaccessible from the front-end.

Note: Multisite over-ride password is currently **not encrypted**. [Please see this issue](https://github.com/soup-bowl/wp-simple-smtp/issues/63).

= Can I change the amount of entries shown in the log view? =
This can be adjusted by the 'simple_smtp_log_table_max_per_page' filter. Returning an integer value to this filter hook will adjust the table page limit.

= Does this plugin support WordPress CLI? =
Yes. With [WP-CLI](https://wp-cli.org/) you can perform the following actions:

* `email-log` View the log if enabled.
* `email-test` Send a test email.

To view the available options and help documentation, run `wp help` or `wp help <function name>`.

= Does this plugin work on WordPress Multisite? =
Yes. Each site can have unique settings, unless overriding is on. The network will use the main site settings, so network admin emails will show up in the main site log.

Since version 1.2, network-activating the plugin grants special configuration options for super administrators. This includes the ability to set overrides and configure site admin access.

= Why do I see capital texts next to the input boxes? (Debugging disabled input boxes) = 
To help diagnose disabled input boxes, when the WordPress site is in [debugging mode](https://wordpress.org/support/article/debugging-in-wordpress/), the input fields will show a small debug text to indicate where the setting came from.

* **CONFIG** is the standard method of saving settings via the admin menu.
* **CONST** are overrides typically set in either wp-config.php or your theme's functions.php.
* **MULTISITE** are network-defined overrides set in the Network Mail panel.
* **ENV** are pulled from the machine/server environmental settings. 

= Can I report an issue, or contribute to development? =
Yes! [Please see our GitHub repository here](https://github.com/soup-bowl/wp-simple-smtp) for writing issues and/or making pull requests.

One of the easiest aspects to contribute to is the SMTP quick configuration segment. If you wish to maintain this aspect, suggest a new setting, or report broken entries, see the [SMTP quick config wiki page](https://github.com/soup-bowl/wp-simple-smtp/wiki/SMTP-Quick-Config).

== Changelog ==
