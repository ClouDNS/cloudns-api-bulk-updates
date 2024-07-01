<?php

// Auth ID and Password
define("AUTH_ID", xxx);
define("AUTH_PASS", "xxx");

// IP address of the master server (primary server)
define("MASTER_IP", "xxx.xxx.xxx.xxx");

// Second IP address for master server (it may be IPv6 or IPv4 address)
//define("MASTER_IP2", "xxx.xxx.xxx.xxx");

// Path to the BIND config file
define("BIND_CONFIG_FILE", "/etc/dnscd/named.conf");

// this file will contain a list of files that are not dns zone files and there won't be a request to be added the next time the script runs
define("TMPFILE", "/var/cloudns/cloudns_invalid-zone-names.txt");

if ((file_exists(TMPFILE) && !is_writable(TMPFILE)) || (!file_exists(TMPFILE) && !is_writable(dirname(TMPFILE)))) {
	die("TMPFILE (".TMPFILE.") is not writable. Please make it writable to continue or update the config option to a new path.");
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
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/enhance-slave-zones-add)');
	$content = curl_exec($init);
	curl_close($init);
	return json_decode($content, true);
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']);
}

// gets the content of the file
$invalid_zones = file(TMPFILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// function to get zone names from BIND config file
function getZoneNamesFromConfig($filePath) {
	$zoneNames = [];
	$fileContent = file_get_contents($filePath);
	if ($fileContent !== false) {
		preg_match_all('/zone\s+"([^"]+)"/', $fileContent, $matches);
		if (!empty($matches[1])) {
			$zoneNames = array_map(function($zone) {
				return rtrim($zone, '.');
			}, $matches[1]);
		}
	}
	return $zoneNames;
}

// get zone names from the BIND config file
$zoneNames = getZoneNamesFromConfig(BIND_CONFIG_FILE);

// loops through the zone names
foreach ($zoneNames as $zoneName) {
	// checks if the zone name is invalid and if not adds the slave zone
	if (in_array($zoneName, $invalid_zones)) {
		continue;
	}

	// calling the api
	$response = apiCall('dns/register.json', "domain-name={$zoneName}&zone-type=slave&master-ip=".MASTER_IP);
	// if the api returns the zone is invalid we put it in the file with the invalid zones
	if ($response['status'] == 'Failed') {
		file_put_contents(TMPFILE, $zoneName."\n", FILE_APPEND);
		continue;
	}
	
	if (defined('MASTER_IP2')) {
		apiCall('dns/add-master-server.json', "domain-name={$zoneName}&master-ip=".MASTER_IP2);
	}
}
?>
