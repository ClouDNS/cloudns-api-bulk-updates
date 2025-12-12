<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "");

// Failover settings
define("FAILOVER_ZONE", "qwdqwdqd.com");
define("FAILOVER_RECORD_ID", 0); // record ID can be received from the support or via https://www.cloudns.net/wiki/article/57/
define("FAILOVER_BACKUP_IP", 1); // the number of the backup IP which needs to be updated by dynamic dns


/**
 * Code
 */
if (FAILOVER_BACKUP_IP < 1 || FAILOVER_BACKUP_IP > 5) {
	die("The backup IP number should be between 1 and 5\n");
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']);
}

$record = apiCall('dns/failover-settings.json', 'domain-name='. FAILOVER_ZONE .'&record-id='. FAILOVER_RECORD_ID);
if (isset($record['status']) && $record['status'] == 'Failed') {
	die($record['statusDescription']);
}
if (!isset($record['down_event_handler']) || $record['down_event_handler'] != 2) {
	die("Your failover record should be with down event handler: Replace with working backup IP\n");
}

$my_ip = apiCall('ip/get-my-ip.json', "");
if (isset($my_ip['status']) && $my_ip['status'] == 'Failed') {
	die($my_ip['statusDescription']);
}
if (empty($my_ip['ip'])) {
	die("Unable to retrieve your current IP address.\n");
}

echo "Your current IP is: {$my_ip['ip']}\n";
echo "Backup IP ". FAILOVER_BACKUP_IP ." is : ". $record['backup_ip_'. FAILOVER_BACKUP_IP] ."\n";

if ($record['backup_ip_'. FAILOVER_BACKUP_IP] == $my_ip['ip']) {
	echo "IPs are equal. No update required.\n";
} else {
	echo "Updating... ";
	
	$record['backup_ip_'. FAILOVER_BACKUP_IP] = $my_ip['ip'];
	
	$data = 'domain-name='. FAILOVER_ZONE .'&record-id='. FAILOVER_RECORD_ID ."&check_type={$record['check_type']}&down_event_handler={$record['down_event_handler']}&up_event_handler={$record['up_event_handler']}&main_ip={$record['main_ip']}&backup_ip_1={$record['backup_ip_1']}&backup_ip_2={$record['backup_ip_2']}&backup_ip_3={$record['backup_ip_3']}&backup_ip_4={$record['backup_ip_4']}&backup_ip_5={$record['backup_ip_5']}";
	
	if (isset($record['check_settings']['host'])) {
		$data .= "&host={$record['check_settings']['host']}";
	}
	if (isset($record['check_settings']['port'])) {
		$data .= "&port={$record['check_settings']['port']}";
	}
	if (isset($record['check_settings']['path'])) {
		$data .= "&path={$record['check_settings']['path']}";
	}
	if (isset($record['check_settings']['content'])) {
		$data .= "&content={$record['check_settings']['content']}";
	}
	if (isset($record['check_settings']['query_type'])) {
		$data .= "&query_type={$record['check_settings']['query_type']}";
	}
	if (isset($record['check_settings']['response'])) {
		$data .= "&query_response={$record['check_settings']['response']}";
	}

	$response = apiCall('dns/failover-modify.json', $data);
	if (empty($response)) {
		echo "Failed. Try again latter.\n";
	} else {
		echo "{$response['statusDescription']}\n";
	}
}


// function to connect to the API
function apiCall ($url, $data) {
	$url = "http://api.cloudns.local/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";

	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/failover-backup-ip-with-dynamic-dns)');

	$content = curl_exec($init);

    if (PHP_VERSION_ID < 80000) {
        curl_close($init);
    }

	return json_decode($content, true);
}
