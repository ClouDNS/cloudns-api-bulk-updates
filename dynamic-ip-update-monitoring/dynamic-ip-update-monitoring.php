<?php

// Auth ID and Password
define("AUTH_ID", XXX);
define("AUTH_PASS", "XXXXXX");

//Monitoring checks IDs
$list = array(1,2,3,4,5);

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/dynamic-ip-update-monitoring)');
	
	$content = curl_exec($init);
	
	curl_close($init);
	
	return json_decode($content, true);
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']);
}
$ip = apiCall('ip/get-my-ip.json', "");
foreach ($list as $id) {
		$check = apiCall('monitoring/get-monitoring-check.json', "id={$id}");
		$response = array();
		if ($check['check_type'] == '1') {
        	$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}");
        }
        if ($check['check_type'] == '2') {
        	$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}");
        }
        if ($check['check_type'] == '3') {
        	$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}");
        }
        if ($check['check_type'] == '4') {
        	$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}&host={$check['host']}&port={$check['port']}&path={$check['path']}");
        }
        if ($check['check_type'] == '5') {
        	$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}&host={$check['host']}&port={$check['port']}&path={$check['path']}");
        }
        if ($check['check_type'] == '6') {
        	$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}&host={$check['host']}&port={$check['port']}&path={$check['path']}&content={$check['content']}");
        }
    	if ($check['check_type'] == '7') {
       		$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}&host={$check['host']}&port={$check['port']}&path={$check['path']}&content={$check['content']}");
       	}
    	if ($check['check_type'] == '8') {
        	$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&port={$check['port']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}&open_port={$check['open_port']}");
        }
    	if ($check['check_type'] == '9') {
        	$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&port={$check['port']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}&open_port={$check['open_port']}");
       	}
       	if ($check['check_type'] == '10') {
       		$response = apiCall('monitoring/update.json', "id={$check['id']}&check_type={$check['check_type']}&name={$check['name']}&status_change_checks={$check['status_change_checks']}&ip={$ip['ip']}&monitoring_region={$check['monitoring_region']}&check_period={$check['check_period']}&state={$check['state']}&query_type={$check['query_type']}&query_response={$check['query_response']}&host={$check['host']}");
	}
	if (isset($response['status']) && $response['status'] == 'Success' ) {
            echo 'The IP address of ', $check['name'],' was changed to ', $ip['ip'], "\n";
	} else {
    	echo 'FAILED to change the IP address of ', $check['name'],' to ', $ip['ip'], "\n";
    }
}
