<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");
// IP address of the master server
define("MASTER_IP", "xxx.xxx.xxx.xxx");
// the directory with the zone files, their names are used to create the slave zones, not the content of the files
define("ZONES_DIR", "/var/named/run-root/var");
// this file will contain a list of files that are not dns zone files and there won't be a request to be added the next time the script runs
define("TMPFILE", "/tmp/cloudns_invalid-zone-names.txt");

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
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/plesk-slave-zones-add)');
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
$fopen = fopen(TMPFILE, "a+");

// gets the content of the file
$invalid_zones = file(TMPFILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// gets the zone files names
if ($handle = opendir(ZONES_DIR)) {
	// loops through the files
	while (false !== ($zoneName = readdir($handle))) {
		// skips the parent dirs
		if ($zoneName != "." && $zoneName != "..") {
			// checks if the zone name is invalid and if not adds the slave zone
			if (!in_array($zoneName, $invalid_zones)) {
				//calling the api
				$response = apiCall('dns/register.json', "domain-name={$zoneName}&zone-type=slave&master-ip=".MASTER_IP);
				// if the api returns the zone is invalid we put it in the file with the invalid zones
				if ($response['status'] == 'Failed') {
					file_put_contents(TMPFILE, $zoneName."\n", FILE_APPEND);
				}
			}
		}
	}

	closedir($handle);
}
fclose($fopen);