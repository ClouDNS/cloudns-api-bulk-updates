<?php

// Auth ID and Password
define("AUTH_ID", xxx);
define("AUTH_PASS", "xxx");

//RECORD PARAMETERS
$add = array('record-type' =>  'TXT' , 'host'=> '_dmarc' , 'record'=> 'v=DMARC1;' , 'ttl' => '3600');


//DNS zones where to add the records - one per row
$zones = "example.com
testzone.net";

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/bulk-records-add-to-zones-list)');
	
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


foreach (explode("\n", $zones) as $zone) {
		$zone = trim($zone);
		if (empty($zone)) {
			continue;
		}
		$params = "domain-name={$zone}";
		foreach ($add as $key => $value) {
			$params .= "&{$key}=". urlencode($value);
		}
		$response = apiCall('dns/add-record.json', $params);
		if ($response['status'] == 'Success') {
				echo 'The ',$add['record-type'],' record was added to ', $zone, "\n";	
		} else {
    		echo "FAILED TO ADD THE RECORD IN ", $zone, "\n";
		}
}
