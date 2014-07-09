zoopaz-reminders
================

A simple webapp that interfaces with the UNIX at command to send simple email notifications. Use a carrier email address for text messages.

Setup
=====

Add a similar line to /etc/sudoers so that your webserver user can execute the `at` command.

    ALL ALL=NOPASSWD: /usr/bin/at

Copy `example.config.php` to `config.php` and add all of the email address for which you want to receive notifications.

If you use a carrier email to text address you can send text messages easily. Here's a large list of carriers and the format.

[http://www.emailtextmessages.com/](http://www.emailtextmessages.com/)
