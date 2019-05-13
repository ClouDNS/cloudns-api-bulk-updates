<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxxxxx");
define("CLOUD_MASTER", "xxxxxxx");

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/delete-cloud-domains)');
	$content = curl_exec($init);
	curl_close($init);
	return json_decode($content, true);
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']."\n");
}

$cloudDomains = apiCall('dns/list-cloud-domains.json', "domain-name=".CLOUD_MASTER);
$count = count($cloudDomains);

if ($count <= 0) {
	echo "There are no cloud domains in the cloud of ".CLOUD_MASTER."\n";
	exit;
}

$z=0;
foreach ($cloudDomains as $domain) {
	$response = '';
	$response = apiCall('dns/delete-cloud-domain.json', "domain-name={$domain}");
	
	if (isset($response['status'])) {
		echo "{$domain} was deleted!\n";
	} else {
		echo "{$domain} was NOT deleted!\n";
	}
	$z++;
}

echo "\nDeleted cloud domains: {$z} of ".count($cloudDomains)."\n";