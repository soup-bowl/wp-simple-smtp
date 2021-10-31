# Contributing Guidelines
Thank you for wanting to contribute to the Simple SMTP WordPress plugin. This plugin intends to remain open-source and devoid of any form of sponsorship to avoid contributing to the growing problem of WordPress plugin-derived spam. All PRs and issue submissions are accepted and happy to have people on board.

If you disagree with any of the guidelines, please note that this plugin is licensed under the MIT license. You are welcome to derrive the codebase to suit your needs or direction as suited. Attribution is welcomed but not necessary. 

## Reporting Bugs, Suggestions and Discussions
Reporting issues, bugs and suggestions begin at the [issue selection screen][bug].

The most common kind of report will be a bug report. Please ensure you select **Bug report** in the issue report selection. This will auto-populate the necessary fields for the report to be identified, and provide a good starting description to help collect your thoughts before reporting. Bug priority is up to maintainer discretion.

Discussions are welcome on the Issues segment. Please mark these under the 'Question' label as to not show them up as actionable bugs.

Support is based on community availability, and the plugin has no official support channels. Reporting on the [plugin support channel](https://wordpress.org/support/plugin/simple-smtp/) on the WordPress directory, and has the best visibility from other plugin users. Please understand that most - if not all - SMTP server interactions may not be bugs caused by the plugin, but could relate to either [WordPress] or the [PHPMailer] library. 

## Pull Request Guidelines
It is preferable that pull requests have a counterpart issue created and linked to in the PR, allowing for the issue to describe the problem that was encountered (or suggestion it is based upon), and the PR to describe the changes and actions made in the developers code.

The project currently has the following PR reviewers:
- [soup-bowl](https://github.com/soup-bowl).

To help speed up the possibility of your PR being merged in, please ensure you run the PHPUnit tests and also run through PHP CodeSniffer to check against [WordPress coding guidelines](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/). Both are provided in the Composer development installation. 

## Attribution
Contributors of pull requests that end up being enrolled in the mainline build will be atrributed in the readme changelog as the person whom contributed the fix, with a link to either the issue, PR, or both. By turn of contributing this will also make you a contributor to the simple SMTP GitHub project.

The attribution status of the plugin on the WordPress plugin directory will not change, and will only reflect those who adjust the Subversion contents. This will always be the author of the plugin as the CI/CD build process utilises the account.

## Releases
For pending merges, releases will occur every other week of the month (week 2 and 4) on the weekend, depending on maintainer availability. Emergency fixes may be pushed through at a sooner date. Conditions for a release in all scenarios are based upon a successful per-commit build execution on the main branch, and successful QA testing.

[bug]: https://github.com/soup-bowl/wp-simple-smtp/issues/new/choose
[WordPress]: https://github.com/WordPress/WordPress
[PHPMailer]: https://github.com/PHPMailer/PHPMailer
