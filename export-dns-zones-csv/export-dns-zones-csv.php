<?php

/**
 * Settings
 */

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

/**
 * Code
 */

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']);
}

$rows_per_page = 100; // 100 is the maximum
$pages = apiCall('dns/get-pages-count.json', "rows-per-page={$rows_per_page}");
for ($i=1; $i<=$pages; $i++) {
	foreach (apiCall('dns/list-zones.json', "page={$i}&rows-per-page={$rows_per_page}") as $page => $zone) {
		echo "\"{$zone['name']}\",\"{$zone['type']}\"\n";
	}
}

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";

	$init = curl_init();
	curl_setopt($init, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);

	$content = curl_exec($init);

	curl_close($init);

	return json_decode($content, true);
}