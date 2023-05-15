<p align="center">
  <img src="https://soupbowl.io/assets/img/wp-ssmtp-150x150.webp" alt="Simple SMTP logo, a red envelope enclosed in a red circle" />
</p>

<h1 align="center">WordPress Simple SMTP</h1>

<p align="center">
  <a href="https://www.codefactor.io/repository/github/soup-bowl/wp-simple-smtp"><img src="https://www.codefactor.io/repository/github/soup-bowl/wp-simple-smtp/badge" />
  <a href="https://github.com/soup-bowl/wp-simple-smtp/actions/workflows/test.yml"><img src="https://github.com/soup-bowl/wp-simple-smtp/actions/workflows/test.yml/badge.svg" alt="Per-commit CI Test" /></a>
  <a href="https://wordpress.org/plugins/simple-smtp/">
  <img src="https://img.shields.io/wordpress/plugin/dm/simple-smtp?logo=wordpress&color=blue" alt="WordPress Plugin Downloads" />
  <img src="https://img.shields.io/wordpress/plugin/installs/simple-smtp?logo=wordpress&color=blue" alt="WordPress Plugin Active Installs" />
  <img src="https://img.shields.io/wordpress/plugin/rating/simple-smtp?logo=wordpress&color=blue" alt="WordPress Plugin Rating" />
  </a>
</p>

Adds a simple, no-fuss SMTP settings to your WordPress installation that lets you define custom settings, which is especially useful for hosts with no control over the php `mail` functionality.

If logging is enabled, a new segment in the settings panel will show up with a 30-day overview of recent emails, and will automatically prune older logs. Please see the FAQ if you want a more permanent solution.

For more information, please see the [project wiki on GitHub](https://github.com/soup-bowl/wp-simple-smtp/wiki).

## Environment and constant overriding (optional)

This plugin will prefer environmental and constant-stored values over the plugin-saved equivalent settings, making it easier to use this plugin via deployment.

These can be either stored in your systems env setup, or in wp-config.php as `define( 'SEE_BELOW', 'your_value_here' );`.

### Accepted Parameters
  
Environment         | Type             | Description
--------------------|------------------|------------
`SMTP_HOST`         | string           | Mail server hostname.
`SMTP_PORT`         | integer          | Port address (usually 25, 465, or 587).
`SMTP_AUTH`         | integer (1 or 0) | Pass below credentials to your mail server (1 or 0).
`SMTP_USER`         | string           | The mail username for this account.
`SMTP_PASS`         | string           | The password for the mailer account.
`SMTP_FROM`         | string           | Enforce all emails come from this email address.
`SMTP_FROMNAME`     | string           | Enforce all emails to have a certain email name.
`SMTP_SEC`          | string           | Use a particular email security method (def, ssl, or tls).
`SMTP_NOVERIFYSSL`  | boolean          | Disable validation of the SMTP server certificate.
`SMTP_LOG`          | boolean          | Controls the logging capability and visibility.
`SMTP_DISABLE`      | boolean          | Disables the mailer. They will still be logged if enabled, but won't send out.

It is recommended to store at least `SMTP_PASS` in your wp-config.php file (with the correct file permissions set). If the openssl extension is available, the plugin will attempt to encrypt the password in the database.
