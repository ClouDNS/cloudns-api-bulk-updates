<?php

// Auth ID and Password
define("AUTH_ID", XXX);
define("AUTH_PASS", "XXXXXX");

//FO Settings
$list = array(1,2,3,4,5);
$domainName = 'XXX.XXX';

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/dynamic-ip-fo-update)');
	
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
$ip = apiCall('ip/get-my-ip.json', "");
foreach ($list as $id) {
		$check = apiCall('dns/failover-settings.json', "domain-name={$domainName}&record-id={$id}");
		$response = array();
		if ($check['check_type'] == '1') {
        	$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");
        }
        if ($check['check_type'] == '2') {
        	$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");
        }
        if ($check['check_type'] == '3') {
        	$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");
        }
        if ($check['check_type'] == '4') {
        	$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&host={$check['check_settings']['host']}&port={$check['check_settings']['port']}&path={$check['check_settings']['path']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");	
        	var_dump($response);
        }
        if ($check['check_type'] == '5') {
        	$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&host={$check['check_settings']['host']}&port={$check['check_settings']['port']}&path={$check['check_settings']['path']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");
        }
        if ($check['check_type'] == '6') {
        	$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&host={$check['check_settings']['host']}&port={$check['check_settings']['port']}&path={$check['check_settings']['path']}&content={$check['check_settings']['content']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");
        }
    	if ($check['check_type'] == '7') {
       		$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&host={$check['check_settings']['host']}&port={$check['check_settings']['port']}&path={$check['check_settings']['path']}&content={$check['check_settings']['content']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");
       	}
    	if ($check['check_type'] == '8') {
        	$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&port={$check['check_settings']['port']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");
        }
    	if ($check['check_type'] == '9') {
        	$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&port={$check['check_settings']['port']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");
       	}
       	if ($check['check_type'] == '10') {
       		$response = apiCall('dns/failover-modify.json', "domain-name={$domainName}&record-id={$id}&check_type={$check['check_type']}&main_ip={$ip['ip']}&state={$check['state']}&query_type={$check['check_settings']['query_type']}&query_response={$check['check_settings']['response']}&host={$check['check_settings']['host']}&down_event_handler=0&up_event_handler=0&check_region={$check['check_region']}");
		}
		if (isset($response['status']) && $response['status'] == 'Success' ) {
				echo 'The IP address was changed to ', $ip['ip'], "\n";
		} else {
    		echo 'FAILED to change the IP address to ', $ip['ip'], "\n";
		}
}
