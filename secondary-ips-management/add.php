<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxxx");

// Extra Slave IP address for the listed master zones - it can be both IPv4 or IPv6
define("SLAVE_IP", "xxx.xxx.xxx.xxx");

// list with zones where the slave ip to be added
$zones = "example.com
example.net";

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/secondary-ips-management/add.php)');
	$content = curl_exec($init);
	curl_close($init);
	return json_decode($content, true);
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']);
}

foreach (explode("\n", $zones) as $zone) {
	$zone = trim($zone);
	if (empty($zone)) {
		continue;
	}
	
	$response = apiCall('dns/axfr-add.json', "domain-name={$zone}&ip=".SLAVE_IP);
	if (isset($response['status'])) {
		echo "{$zone}: {$response['statusDescription']}\n";
	} else {
		echo "{$zone}: slave IP added\n";
	}
}
