# last-month-hourly-stats.php
With this script you can send an e-mail report with a hourly DNS statistics for the last month for a list with DNS zones.

# Configuration
**AUTH_ID** should be your API user ID
**AUTH_PASS** should be the password of your API user

In the **$dns_zones** variable you have to add a list with DNS zone names for which you want to get reports.

In the **$mail_to** variable you have to add a list with e-mail addresses where you want reports to be send.

In the **$mail_from**  variable you have to add an e-mail address from which the reports will be send.

```php
<?php

// list with the DNS zones you want to get reports
$dns_zones = array(
	'example.com',
	'example.net',
);

// list with the e-mails where will be send the reports
$mail_to = array(
	'mail@example.net',
	'mail@example.com',
);

// e-mail which will be used for the sending of the reports
$mail_from = 'report@example.net';
```

# Usage
When you are ready with your configuration, you can run the script with following command:
```
php last-month-hourly-stats.php
```

# Output
The script will output a list with the domain names for which the reports are generated and sent.

# Possible problems
If your API user ID or Password are incorrect, you will get an error message.

If you are entered an invalid DNS zones in the list, you will get an error message.

# Cron job
You can add this script to be executed each first day from the month.
Example:
```
0 0 1 * * /usr/bin/php /path/to/last-month-hourly-stats.php >/dev/null
```

# ClouDNS Links
* [DNS hosting](https://www.cloudns.net)
* [Managed DNS](https://www.cloudns.net/managed-dns/)
* [DDoS Protected DNS](https://www.cloudns.net/ddos-protected-plans/)