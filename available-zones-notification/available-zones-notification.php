<?php

// Auth ID and Password
define('AUTH_ID', 0);
define('AUTH_PASS', '');

// settings
$mail_notification_from = 'box@from.mail';
$mail_notification_to = 'box@to.mail';
$available_zones_limit = 100;

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

// checking if we can log in successfully
$login = apiCall('dns/login.json', '');
if (isset($login['status']) && $login['status'] == 'Failed') {
	echo $login['statusDescription'] ,"\n";
	exit;
}

$zones = apiCall('dns/get-zones-stats.json', '');
if (!isset($zones['count']) || !isset($zones['limit'])) {
	echo 'Internal error';
	exit;
}

$available_slots = $zones['limit'] - $zones['count'];
if ($available_slots <= $available_zones_limit) {
	$subject = "ClouDNS: {$available_slots} free zone slots";
	$message = "Hello,\n\nYou have only {$available_slots} free DNS zone slots in your account. This is an hourly notification.\n\nRegards,\nClouDNS";
	$headers = "From: {$mail_notification_from}\r\n";
	mail($mail_notification_to, $subject, $message, $headers);
	echo "You are notified, because the free DNS zone slots are {$available_slots}";
	exit;
} else {
	echo "You are not notified, because the free DNS zone slots are {$available_slots}";
	exit;
}
