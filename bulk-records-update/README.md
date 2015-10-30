# Bulk records update
With this script you can bulk update records. You can filter the records by "Type" and "Points to".

# Configuration
*AUTH_ID* should be your API user ID
*AUTH_PASS* should be the password of your API user

In the *$search* variable you have add the record type and record you are looking for. For example you can search for *A* records with IP address *1.2.3.4*

In the *$update* variable you have to add the record you want to be set to the found records. For example you can update the found A records with IP 1.2.3.4 to *4.3.2.1*

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