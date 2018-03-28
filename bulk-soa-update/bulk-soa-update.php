<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// SOA record to be set to all zones
$soa = array(
	'primary-ns' => 'pns1.cloudns.net',
	'admin-mail' => 'support@cloudns.net',
	'refresh' => 7200,
	'retry' => 1800,
	'expire' => 1209600,
	'default-ttl' => 3600,
);

/*
 * The configuration end here
 * 
 * Do not change anything below this lines, 
 * if you do not know what you are doing
 */

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']);
}

// getting all the domains
$rows_per_page = 100; // 100 is the maximum
$z = $y = 0;
$pages = apiCall('dns/get-pages-count.json', "rows-per-page={$rows_per_page}");
for ($i=1; $i<=$pages; $i++) {
	foreach (apiCall('dns/list-zones.json', "page={$i}&rows-per-page={$rows_per_page}") as $page => $zone) {
		if ($zone['type'] != 'master') {
			continue;
		}
		$z++; // counts the zones that can be updated
		$response = apiCall('dns/modify-soa.json', "domain-name={$zone['name']}&primary-ns={$soa['primary-ns']}&admin-mail={$soa['admin-mail']}&refresh={$soa['refresh']}&retry={$soa['retry']}&expire={$soa['expire']}&default-ttl={$soa['default-ttl']}");
		if (isset($response['status']) && $response['status'] == 'Failed') {
			echo $zone['name']." was not updated\n";
		} else {
			echo $zone['name']." was updated successfully\n";
			$y++; // counts the zones that are updated
		}
	}
}

echo "Updated zones: {$y} of {$z} master zones\n";

function apiCall ($url, $data) {
	$url = "http://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	$content = curl_exec($init);
	curl_close($init);
	return json_decode($content, true);
}

