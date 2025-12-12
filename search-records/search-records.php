<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// search criteria
$search = array(
	'type' => 'A',		// type of the records you want to search for, mandatory field
	'record' => '1.2.3.4',	// current value of the records, mandatory field
);

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/search-records)');
	
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

// getting all the domains
$rows_per_page = 100; // 100 is the maximum
$pages = apiCall('dns/get-pages-count.json', "rows-per-page={$rows_per_page}");
$foundAnyRecords = false;

for ($i=1; $i<=$pages; $i++) {
	foreach (apiCall('dns/list-zones.json', "page={$i}&rows-per-page={$rows_per_page}") as $page => $zone) {
		if ($zone['type'] != 'master') {
			continue;
		}
		$records = apiCall('dns/records.json', "domain-name={$zone['name']}");
		$searchResult = [];
		$foundCounter = 0;
		
		if (empty($records)) {
			continue;
		}
		
		if (isset($records['status']) && $records['status'] == 'Failed') {
			echo $records['statusDescription'];
		}
		
		foreach ($records as $record) {
			if ($record['type'] == $search['type'] && $record['record'] == $search['record']) {
				$foundAnyRecords = true;
				$foundCounter++;
				$searchResult[$record['id']]['host'] = $record['host'];
				$searchResult[$record['id']]['type'] = $record['type'];
				$searchResult[$record['id']]['record'] = $record['record'];
			}
		}
		
		if (!empty($searchResult)) {
			$resultText = "{$foundCounter} record".($foundCounter > 1 ? 's were' : ' was')." found in zone {$zone['name']}";
			echo "\n".$resultText."\n";
			echo str_repeat("-", strlen($resultText));
			echo "\n";
			foreach ($searchResult as $result) {
				echo "{$result['host']}".($result['host'] != '' ? '.' : '')."{$zone['name']}  {$result['type']}  {$result['record']}\n";
			}
			echo "\n";
		}
	}
}

if ($foundAnyRecords == false) {
	echo "No records found!\n";
}
