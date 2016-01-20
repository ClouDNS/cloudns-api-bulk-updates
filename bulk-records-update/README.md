# Bulk records update
With this script you can bulk update records. You can filter the records by "Type" and "Points to".

# Configuration
**AUTH_ID** should be your API user ID
**AUTH_PASS** should be the password of your API user

In the **$search** variable you have to add the record type and record you are looking for. For example you can search for **A** records with IP address **1.2.3.4**

In the **$update** variable you have to add the record you want to be set to the found records. For example you can update the found A records with IP 1.2.3.4 to **4.3.2.1**

```php
<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// search criteria
$search = array(
	'type' => 'A',		// type of the records you want to update, mandatory field
	'record' => '1.2.3.4',	// current value of the records, mandatory field
);

// what will be updated
$update = array(
	'record' => '4.3.2.1',	// what record you want to be set on the found DNS records
);
```

# Usage
When you are ready with your configuration, you can run the script with following command:
```
php bulk-records-update.php
```

# Output
The script will output all found records and status of the update.

# Possible problems
If your API user ID or Password are incorrect, you will get an error message.

If any of the found records is not update, you will get an error message

# ClouDNS Links
* [DNS hosting](https://www.cloudns.net)
* [Managed DNS](https://www.cloudns.net/managed-dns/)
* [DDoS Protected DNS](https://www.cloudns.net/ddos-protected-plans/)