<?php
	##########################################################################################################################################
	### Base Connection variables & Functions -S
	### GROUP_ID is the DNS Zone Group ID that your zones belong to
	##########################################################################################################################################
	define("AUTH_ID", XXX);
	define("AUTH_PASS", "XXX");
	define("GROUP_ID", XXX);
	define("MASTER_IP", "XXX");
	define("PDNS_MYSQL_HOST", "localhost");
	define("PDNS_MYSQL_PORT", "3306");
	define("PDNS_MYSQL_USER", "XXX");
	define("PDNS_MYSQL_PASS", "XXX");
	define("PDNS_MYSQL_DB", "XXX");
	##########################################################################################################################################
	### Base Connection variables & Functions -E
	##########################################################################################################################################
	function apiCall ($url, $data) {
		$url = "https://api.cloudns.net/{$url}";
		$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
		$init = curl_init();
		curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($init, CURLOPT_URL, $url);
		curl_setopt($init, CURLOPT_POST, true);
		curl_setopt($init, CURLOPT_POSTFIELDS, $data);
		curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/powerdns-mysql-zone-delete-by-group.php)');
		$content = curl_exec($init);
		curl_close($init);
		return json_decode($content, true);
		echo json_decode($content, true);
	}
	$login = apiCall('dns/login.json', "");
	if (isset($login['status']) && $login['status'] == 'Failed') { die($login['statusDescription']."\n"); }
	##########################################################################################################################################
	### Confirm the ability to establish a MySQL Database Connection
	##########################################################################################################################################
	$db = mysqli_connect(PDNS_MYSQL_HOST, PDNS_MYSQL_USER, PDNS_MYSQL_PASS, PDNS_MYSQL_DB, PDNS_MYSQL_PORT);
	if (!$db) { die("Unable to connect to the database.\n"); }
	##########################################################################################################################################
	### Prepare an Empty Array for your PDNS Zone List.
	##########################################################################################################################################
	$powerdns_zones = array();
	
	$sql = 'SELECT "name" FROM "domains" WHERE "type"=\'MASTER\'';
	$result = pg_query($db, $sql);
	while ($zone = pg_fetch_assoc($result)) {
		$powerdns_zones[] = $zone['name'];
	}
	##########################################################################################################################################
	### Set Max-Selection/Pagination // 100 is the highest supported limit
	##########################################################################################################################################
	$rows_per_page = 100; 
	### Make sure we're filtering by Group ID
	$pages = apiCall('dns/get-pages-count.json', "rows-per-page={$rows_per_page}&group-id=".GROUP_ID);
	for ($i=1; $i<=intval($pages); $i++) {
		// Make sure we're STILL filtering by Group ID
		$cloudns_zones = apiCall('dns/list-zones.json', "page={$i}&rows-per-page={$rows_per_page}&group-id=".GROUP_ID);
		foreach ($cloudns_zones as $page => $zone) {
			// Make sure we're only checking Slave Zones
			if ($zone['type'] != 'slave') { continue; }		
			if (!in_array($zone['name'], $powerdns_zones)) {
				$response = apiCall('dns/delete.json', "domain-name={$zone['name']}");
				if ($response['status'] == 'Success') { echo "{$zone['name']} is deleted\n"; } else { echo "{$zone['name']} cannot be deleted: {$response['statusDescription']}\n"; }
			}
		}
	}
	##########################################################################################################################################
	### Ahmed Samir - asamir@digitalfyre.com
	##########################################################################################################################################
