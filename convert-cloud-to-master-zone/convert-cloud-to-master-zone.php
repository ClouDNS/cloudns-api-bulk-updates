<?php

/**
 * Settings
 */

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// list with Cloud Masters
$masters = array(
	'cloud-master1.com',
	'cloud-master2.com',
);

/**
 * Code
 */

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']."\n");
}
if (empty($masters)) {
	die("You need to specify at least one cloud master\n");
}
foreach ($masters as $master) {
	$cloudDomains = apiCall('dns/list-cloud-domains.json', "domain-name={$master}");
	if (isset($cloudDomains['status']) && $cloudDomains['status'] == 'Failed') {
		echo " \t{$master}: {$cloudDomains['statusDescription']}\n";
		continue;
	}
	
	echo "Obtaining the cloud domains of {$master}\n";
	if (empty($cloudDomains)) {
		echo "\tThere are no zones in the cloud of {$master}\n\n";
		continue;
	}
	foreach ($cloudDomains as $cloudDomain) {
		echo "\tDeleting {$cloudDomain}...";
		$delete = apiCall('dns/delete-cloud-domain.json', "domain-name={$cloudDomain}");
		if ($delete['status'] == 'Failed') {
			echo " Failed. Switching to the next one\n\n";
			continue;
		}
		echo " Success.\n";
		
		echo "\tCreating master zone {$cloudDomain}...";
		$register = apiCall('dns/register.json', "domain-name={$cloudDomain}&zone-type=master");
		if ($register['status'] == 'Failed') {
			echo " Failed. You will have to create the zone manually\n";
		}
		echo " Success.\n";
		
		echo "\tAdding the records of {$master} to {$cloudDomain}...";
		$copy = apiCall('dns/copy-records.json', "domain-name={$cloudDomain}&from-domain={$master}&delete-current-records=1");
		if ($copy['status'] == 'Failed') {
			echo " Failed. Switching to the next one\n\n";
		}
		echo " Success.\n\n";
	}
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

	$content = curl_exec($init);

	if (PHP_VERSION_ID < 80000) {
        curl_close($init);
    }

	return json_decode($content, true);
}
