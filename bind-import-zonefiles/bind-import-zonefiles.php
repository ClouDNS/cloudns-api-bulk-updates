<?php

/**
 * Settings
 */

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "");
define("ZONEFILES_DIRECTORY", "/path/to/your/zones/");

/**
 * Code
 */

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']);
}

foreach (glob(ZONEFILES_DIRECTORY .'/*') as $filename) {
	$zone = basename($filename);
	$zonefile = file_get_contents($filename);
	
	$response = apiCall('dns/register.json', "domain-name={$zone}&zone-type=master");
	if (!isset($response['status'])) {
		echo "{$zone} cannot be added: failed to connect\n";
		continue;
	}
	if ($response['status'] == 'Failed') {
		echo "{$zone} cannot be added: {$response['statusDescription']}\n";
		continue;
	}
	echo "{$zone} created\n";
	
	$response = apiCall('dns/records-import.json', "domain-name={$zone}&format=bind&delete-existing-records=1&content={$zonefile}");
	if (!isset($response['status'])) {
		echo "{$zone} cannot be imported\n";
		continue;
	}
	if ($response['status'] == 'Failed') {
		echo "{$zone} cannot be imported: {$response['statusDescription']}\n";
		continue;
	}
	echo "{$zone} imported\n";
}

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";

	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/zones-import-via-transfer)');

	$content = curl_exec($init);

    if (PHP_VERSION_ID < 80000) {
        curl_close($init);
    }

	return json_decode($content, true);
}

