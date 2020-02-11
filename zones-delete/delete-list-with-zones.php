<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxxxxx");

// list with zones you want to delete (one per row)
$list_with_zones = "zone1.com
zone2.com";

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/delete-list-with-zones)');
	$content = curl_exec($init);
	curl_close($init);
	return json_decode($content, true);
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']."\n");
}


$zones = explode("\n", $list_with_zones);
foreach ($zones as $domain) {
	$response = apiCall('dns/delete.json', "domain-name=".$domain);
	if (isset($response['status'])) {
		echo "{$domain} was deleted!\n";
	} else {
		echo "{$domain} was NOT deleted!\n";
	}
	$z++;
}

echo "\nDeleted zones: {$z} of ".count($zones)."\n";