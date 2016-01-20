# Available DNS zone slots notification
With this script you can configure an automatically notification to your e-mail for low number of available DNS zone slots in your account.

# Configuration
**AUTH_ID** should be your API user ID
**AUTH_PASS** should be the password of your API user

In the **$mail_notification_from** variable you have to add the e-mail address from which will be send the message

In the **$mail_notification_to** variable you have to add the e-mail address to which will be send the notification message

In the **$available_zones_limit** variable you have to add the limit for available zone slots when you want to receive a notification

```php
<?php

// Auth ID and Password
define('AUTH_ID', 0);
define('AUTH_PASS', 'xxx');

// settings
$mail_notification_from = 'box@from.mail';
$mail_notification_to = 'box@to.mail';
$available_zones_limit = 100;
```

# Usage
When you are ready with your configuration, you can run the script with following command:
```
php available-zones-notification.php
```

# Output
The script will output a message with information, if you are notified or not.

# Possible problems
If your API user ID or Password are incorrect, you will get an error message.

# Cron job
You can add this script as a daily cron job on your server.
Example:
```
0 9 * * * /usr/bin/php available-zones-notification.php >/dev/null
```

# ClouDNS Links
* [DNS hosting](https://www.cloudns.net)
* [Managed DNS](https://www.cloudns.net/managed-dns/)
* [DDoS Protected DNS](https://www.cloudns.net/ddos-protected-plans/)