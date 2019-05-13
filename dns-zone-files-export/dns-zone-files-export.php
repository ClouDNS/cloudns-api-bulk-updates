<?php

/**
 * Settings
 */

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");
// the directory where the zone files will be stored
define("ZONES_DIR", "/path/to/directory");

/**
 * Code
 */

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']."\n");
}
// if the zone directory doesn't exist it will be created with full permissions
if (!is_dir(ZONES_DIR)) {
	mkdir(ZONES_DIR, 0777, true);         
}

$totalZones = $exportedZones = 0;
$rows_per_page = 100; // 100 is the maximum
$pages = apiCall('dns/get-pages-count.json', "rows-per-page={$rows_per_page}");
for ($i=1; $i<=$pages+1; $i++) {
	foreach (apiCall('dns/list-zones.json', "page={$i}&rows-per-page={$rows_per_page}") as $zone) {
		if ($zone['type'] != 'master' || !in_array($zone['zone'], array('domain', 'ipv4', 'ipv6'))) {
			continue;
		}
		
		$totalZones++;
		echo "Exporting {$zone['name']}... \n";
		
		$response = apiCall('dns/records-export.json', "domain-name={$zone['name']}");
		if (isset($response['status']) && $response['status'] == 'Failed') {
			echo " \t{$response['statusDescription']}\n";
		} else {
			$fileName = str_replace('/', '_', $zone['name']);
			if (file_put_contents(ZONES_DIR.'/'.$fileName, $response['zone'])) {
				echo " \tSuccess!\n";
				$exportedZones++;
			} else {
				echo " \tFailed!\n";
			}
		}
	}
}
echo "\nTotal zones exported: ".$exportedZones." out of ".$totalZones." master zones\n";

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";

	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/dns-zone-files-export)');

	$content = curl_exec($init);

	curl_close($init);

	return json_decode($content, true);
}