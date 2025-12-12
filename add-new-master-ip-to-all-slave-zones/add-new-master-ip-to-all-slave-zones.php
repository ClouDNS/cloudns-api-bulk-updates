<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// Extra Master IP address for the slave zones - it can be both IPv4 or IPv6
define("MASTER_IP", "xxx.xxx.xxx.xxx");

// Remove current Master IP address(es) for the slave zones - change to 1 to remove them
define("REMOVE_CURRENT", 0);

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
	die($login['statusDescription']);
}


// getting all the domains
$rows_per_page = 100; // 100 is the maximum
$pages = apiCall('dns/get-pages-count.json', "rows-per-page={$rows_per_page}");

for ($i=1; $i<=$pages; $i++) {
	foreach (apiCall('dns/list-zones.json', "page={$i}&rows-per-page={$rows_per_page}") as $page => $zone) {
		if ($zone['type'] != 'slave') {
			continue;
		}
		
		if (REMOVE_CURRENT == 1) {
			$currentMasterServers = apiCall('dns/master-servers.json', "domain-name={$zone['name']}");
		}
		
		$response = apiCall('dns/add-master-server.json', "domain-name={$zone['name']}&master-ip=".MASTER_IP);
		
		if ($response['status'] == "Success" && REMOVE_CURRENT == 1) {
			foreach ($currentMasterServers as $key => $value) {
				apiCall('dns/delete-master-server.json', "domain-name={$zone['name']}&master-id={$key}");
			}
			echo "{$zone['name']}: current master server(s) removed\n"; 
		}
		
		echo "{$zone['name']}: {$response['statusDescription']}\n";
	}
}

