<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// PowerDNS pgsql configuration
define("PDNS_PGSQL_HOST", "xxx.xxx.xxx.xxx");
define("PDNS_PGSQL_USER", "xxx");
define("PDNS_PGSQL_PASS", "xxx");
define("PDNS_PGSQL_DB", "xxx");
define("PDNS_PGSQL_PORT", "5432");

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	$content = curl_exec($init);
    if (PHP_VERSION_ID < 80000) {
        curl_close($init);
    }
	return json_decode($content, true);
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']."\n");
}

// check db connection
$db = pg_connect('host='.PDNS_PGSQL_HOST.' port='.PDNS_PGSQL_PORT.' dbname='.PDNS_PGSQL_DB.' user='.PDNS_PGSQL_USER.' password='.PDNS_PGSQL_PASS);
if (!$db) {
	die("Unable to connect to the database.\n");
}

$powerdns_zones = array();

$sql = 'SELECT "name" FROM "domains" WHERE "type"=\'MASTER\'';
$result = pg_query($db, $sql);
while ($zone = pg_fetch_assoc($result)) {
	$powerdns_zones[] = $zone['name'];
}

$rows_per_page = 100; // 100 is the maximum
$pages = apiCall('dns/get-pages-count.json', "rows-per-page={$rows_per_page}");
for ($i=1; $i<=intval($pages); $i++) {
	$cloudns_zones = apiCall('dns/list-zones.json', "page={$i}&rows-per-page={$rows_per_page}");
	foreach ($cloudns_zones as $page => $zone) {
		if ($zone['type'] != 'slave') {
			continue;
		}
		
		if (!in_array($zone['name'], $powerdns_zones)) {
			$response = apiCall('dns/delete.json', "domain-name={$zone['name']}");
			if ($response['status'] == 'Success') {
				echo "{$zone['name']} is deleted\n";
			} else {
				echo "{$zone['name']} cannot be deleted: {$response['statusDescription']}\n";
			}
		}
	}
}


