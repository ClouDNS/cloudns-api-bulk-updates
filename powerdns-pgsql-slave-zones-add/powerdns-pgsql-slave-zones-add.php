<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// IP address of the master server (primary server)
define("MASTER_IP", "xxx.xxx.xxx.xxx");

// Second IP address for master server (it may be IPv6 or IPv4 address)
//define("MASTER_IP2", "xxx.xxx.xxx.xxx");


// PowerDNS pgsql configuration
define("PDNS_PGSQL_HOST", "xxx.xxx.xxx.xxx");
define("PDNS_PGSQL_USER", "xxx");
define("PDNS_PGSQL_PASS", "xxx");
define("PDNS_PGSQL_DB", "xxx");
define("PDNS_PGSQL_PORT", "5432");

// this file will contain a list of zones, which are already added or checked with the HTTP API
define("TMPFILE", "/tmp/cloudns_checked_zones.txt");

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

if (file_exists(TMPFILE)) {
	$checked_zones = file(TMPFILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
} else {
	$checked_zones = array();
}

$sql = 'SELECT "name" FROM "domains" WHERE "type"=\'MASTER\'';
$result = pg_query($db, $sql);
while ($zone = pg_fetch_assoc($result)) {
	if (in_array($zone['name'], $checked_zones)) {
		continue;
	}

	apiCall('dns/register.json', "domain-name={$zone['name']}&zone-type=slave&master-ip=".MASTER_IP);
	file_put_contents(TMPFILE, $zone['name']."\n", FILE_APPEND);
	if (defined('MASTER_IP2')) {
		apiCall('dns/add-master-server.json', "domain-name={$zone['name']}&master-ip=".MASTER_IP2);
	}
}
