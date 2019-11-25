 
<?php
// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "XXXXXX");
// Master IP address for the slave zones, that will be deleted - it can be both IPv4 or IPv6
define("MASTER_IP", "xxx.xxx.xxx.xxx");
// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/remove-master-ip-from-all-slave-zones/remove-master-ip-from-all-slave-zones)');
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
$count = 0;
for ($i=1; $i<=$pages; $i++) {
	foreach (apiCall('dns/list-zones.json', "page={$i}&rows-per-page={$rows_per_page}") as $page => $zone) {
		if ($zone['type'] != 'slave') {
			continue;
		}
		foreach (apiCall('dns/master-servers.json', "domain-name={$zone['name']}") as $id => $ip) {
			if ($ip == MASTER_IP) {
				$response = apiCall('dns/delete-master-server.json', "domain-name={$zone['name']}&master-id={$id}");
				if ($response['status'] == "Success") {
					$count++;
					echo MASTER_IP." removed from ".$zone['name']."\n";
				}
			}
		}
	}
}
$text = "\n".MASTER_IP." is removed from ".$count." Slave zone".($count>1 || $count==0 ? "s" : "").".\n\n";
$textCount = strlen($text);
for ($i=0; $i<=$textCount-4; $i++){
	echo "-";
}				
echo $text;
