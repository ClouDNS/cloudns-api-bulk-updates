# Export DNS zones in CSV format
With this script you can export a full list of your DNS zones with their name and type in CSV format.

# Configuration
**AUTH_ID** should be your API user ID
**AUTH_PASS** should be the password of your API user

```php
<?php

// Auth ID and Password
define('AUTH_ID', 0);
define('AUTH_PASS', 'xxx');
```

# Usage
When you are ready with your configuration, you can run the script with following command:
```
php export-dns-zones-csv.php
```

# Output
The script will output a CSV formatted table with your DNS zones.

# Possible problems
If your API user ID or Password are incorrect, you will get an error message.


# ClouDNS Links
* [DNS hosting](https://www.cloudns.net)
* [Premium DNS](https://www.cloudns.net/premium/)
* [Managed DNS](https://www.cloudns.net/managed-dns/)
* [DDoS Protected DNS](https://www.cloudns.net/ddos-protected-plans/)