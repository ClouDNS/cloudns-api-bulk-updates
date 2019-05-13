<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// IP address of the master server (primary server)
define("MASTER_IP", "xxx.xxx.xxx.xxx");

// Second IP address for master server (it may be IPv6 or IPv4 address)
//define("MASTER_IP2", "xxx.xxx.xxx.xxx");


// PowerDNS mysql configuration
define("PDNS_MYSQL_HOST", "xxx.xxx.xxx.xxx");
define("PDNS_MYSQL_USER", "xxx");
define("PDNS_MYSQL_PASS", "xxx");
define("PDNS_MYSQL_DB", "xxx");
define("PDNS_MYSQL_PORT", "3306");

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
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/powerdns-mysql-slave-zones-add)');
	$content = curl_exec($init);
	curl_close($init);
	return json_decode($content, true);
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']."\n");
}

// check db connection
$db = mysqli_connect(PDNS_MYSQL_HOST, PDNS_MYSQL_USER, PDNS_MYSQL_PASS, PDNS_MYSQL_PORT);
if (!$db) {
	die("Unable to connect to the database.\n");
}

if (file_exists(TMPFILE)) {
	$checked_zones = file(TMPFILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
} else {
	$checked_zones = array();
}

$sql = 'SELECT `name` FROM `domains` WHERE `type`=\'MASTER\'';
$result = mysqli_query($db, $sql);
while ($zone = mysqli_fetch_assoc($result)) {
	if (in_array($zone['name'], $checked_zones)) {
		continue;
	}

	apiCall('dns/register.json', "domain-name={$zone['name']}&zone-type=slave&master-ip=".MASTER_IP);
	file_put_contents(TMPFILE, $zone['name']."\n", FILE_APPEND);
	if (defined('MASTER_IP2')) {
		apiCall('dns/add-master-server.json', "domain-name={$zone['name']}&master-ip=".MASTER_IP2);
	}
}
