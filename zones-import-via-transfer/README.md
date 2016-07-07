# Zones Import Via Transfer
With this script you can create master DNS zones at ClouDNS system and automatically import the DNS records into the created DNS zones from an external DNS server.

# Configuration
**AUTH_ID** should be your API user ID
**AUTH_PASS** should be the password of your API user
**$list** is the list with the DNS zones, one per row in format "domain.com,IP"

```php
<?php

// Auth ID and Password
define('AUTH_ID', 0);
define('AUTH_PASS', 'xxx');

$list = "domain1.com,127.0.0.1
domain2.com,127.0.0.2";
```

# Usage
When you are ready with your configuration, you can run the script with following command:
```
php zones-import-via-transfer.php
```

# Output
The script will output a detailed information for the executed commands

# Possible problems
- If the API user ID or Password are incorrect, you will get an error message.
- If there is an invalid row format, you will get an error message.
- If the zone creation failed, you will get an error message with more details.
- If the records cannot be import, you will get an error message with more details.


# ClouDNS Links
* [DNS hosting](https://www.cloudns.net)
* [Premium DNS](https://www.cloudns.net/premium/)
* [Managed DNS](https://www.cloudns.net/managed-dns/)
* [Anycast DNS](https://www.cloudns.net/anycast-dns)
* [DDoS Protected DNS](https://www.cloudns.net/ddos-protected-plans/)
