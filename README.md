# WordPress Simple SMTP
Adds a simple SMTP configuration panel into the WordPress admin, accessible at
Settings > Mail.

## Environment and constant over-riding
This plugin will prefer Environmental and constant stored values over the
plugin-saved editions, making it easier to use this plugin with deployment.

These can be either stored in your systems env setup, or in wp-config.php as
`define( 'SEE_BELOW', 'your_value_here' );`.

### Accepted Parameters
* `SMTP_HOST` (string) Mail server hostname.
* `SMTP_PORT` (integer) Port address (usually 25, 465 or 587).
* `SMTP_AUTH` (integer, 1 or 0) Pass below credentials to your mail server.
* `SMTP_USER` (string) The mail username for this account.
* `SMTP_PASS` (string) The password for the mailer account.

`SMTP_PASS` is stored in **plaintext**! Where you wish to store it depends on
your configuration, but as a minimum it is recommended to store at least
`SMTP_PASS` in your wp-config.php file.