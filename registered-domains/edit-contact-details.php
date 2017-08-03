<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxxxx");

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	$init = curl_init();
	curl_setopt($init, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	$content = curl_exec($init);
	curl_close($init);
	return json_decode($content, true);
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']."\n");
}

// list of the domain names whose contacts will be changed
$domains = array(
	'domain1.com',
	'domain2.com',
);

$address = 'Some address';
$zip = '000';

$success = array();
foreach ($domains as $key=>$domain) {
	$success[$domain] = array();
	$contacts = apiCall("domains/get-contacts.json", "domain-name={$domain}");
	foreach ($contacts as $group=>$contact) {
		$success[$domain][$group] = false;
		$response = apiCall("domains/set-contacts.json", "domain-name={$domain}&type={$contact['type']}&name={$contact['name']}&company={$contact['company']}&address1={$address}&city={$contact['city']}&country={$contact['country']}&telno={$contact['telno']}&telnocc={$contact['telnocc']}&mail={$contact['mail']}&zip={$zip}");
		if ($response['status'] == 'Success') {
			$success[$domain][$group] = true;
		}
		echo "{$domain}: ".str_replace('The contact', 'The '.$contact['type'].' contact', $response['statusDescription'])."\n";
	}
}
echo "Successfully changed domains: ".count($success)." out of ".count($domains).".\n";