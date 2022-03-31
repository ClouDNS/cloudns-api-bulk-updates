<?php

// Auth ID and Password
define("AUTH_ID", XXX);
define("AUTH_PASS", "XXXX");

//RECORD PARAMETERS
$add = array('record-type' =>  'A' , 'host'=> '' , 'record'=> '' , 'ttl' => '3600');

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/bulk-add-records)');
	
	$content = curl_exec($init);
	
	curl_close($init);
	
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
		if ($zone['type'] != 'master' || $zone['zone'] != 'domain') {
			continue;
		}
		$params = "domain-name={$zone['name']}";
		foreach ($add as $key => $value) {
			$params .= "&{$key}=". urlencode($value);
		}
		$response = apiCall('dns/add-record.json', $params);
		if ($response['status'] = 'Success') {
				echo 'The ',$add['record-type'],' record was added to ', $zone['name'], "\n";	
		} else {
    		echo json_encode($add),": {$response['message']}\n";
		}
	}
}
