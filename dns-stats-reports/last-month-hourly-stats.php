<?php

/**
 * Settings
 */

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// list with the DNS zones you want to get reports
$dns_zones = array(
	'example.com',
	'example.net',
);

// list with the e-mails where will be send the reports
$mail_to = array(
	'mail@example.net',
	'mail@example.com',
);

// e-mail which will be used for the sending of the reports
$mail_from = 'report@example.net';


/**
 * Code
 */

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']);
}

$last_month = strtotime('-1 month');

foreach ($dns_zones as $zone) {
	$xls = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
	for ($i=date('Y-m-d', $last_month); $i<=date('Y-m-d'); $i=date('Y-m-d', strtotime('+1 day', strtotime($i)))) {
		$time = strtotime($i);
		$xls .= '<Worksheet ss:Name="'. date("M d, Y", $time).'"><Table>';
		$year = date('Y', $time);
		$month = date('m', $time);
		$day = date('d', $time);
		$respone = apiCall('dns/statistics-hourly.json', "domain-name={$zone}&year={$year}&month={$month}&day={$day}");
		if (isset($response['status']) && $response['status'] == 'Failed') {
			echo "Statistics for {$zone} failed: {$response['statusDescription']}";
			continue 2;
		}

		foreach ($respone as $day => $requests) {
			$xls .= "<Row><Cell><Data ss:Type=\"String\">{$day}</Data></Cell><Cell><Data ss:Type=\"Number\">{$requests}</Data></Cell></Row>";
		}
		$xls .= '</Table></Worksheet>';
	}
	$xls .= '</Workbook>';
	
	$subject = "{$zone} DNS stats for the last month";
	$message = "Hello,\n\nAs attachment are added DNS statistics for zone {$zone} for the last month.\n\nRegards,\nClouDNS";

	$mail = implode(', ', $mail_to);
	if (!mail_stats($mail, $mail_from, $subject, $message, $xls, date('Y-m-d')."-{$zone}.xls")) {
		echo "The report for {$zone} cannot be send to {$mail}\n";
	} else {
		echo "The report for {$zone} has been send to {$mail}\n";
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
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/dns-stats-reports)');

	$content = curl_exec($init);

	curl_close($init);

	return json_decode($content, true);
}

// function for sending of stats to the email
function mail_stats($mail_to, $from_mail, $subject, $message, $attachment_content, $attachment_filename) {
	$content = chunk_split(base64_encode($attachment_content));
	
	$uid = md5(uniqid(microtime()));
	
	$header = "From: ".$from_mail."\r\n";
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: multipart/mixed;\r\n boundary=\"------------".$uid."\"\r\n";
	$header .= "This is a multi-part message in MIME format.\r\n";
	
	$mail = "--------------".$uid."\r\n";
	$mail .= "Content-type: text/plain; charset=utf-8\r\n";
	$mail .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
	$mail .= $message."\r\n";

	
	$mail .= "--------------{$uid}\r\n";
	$mail .= "Content-Type: application/octet-stream; name=\"".$attachment_filename."\"\r\n";
	$mail .= "Content-Transfer-Encoding: base64\r\n";
	$mail .= "Content-Disposition: attachment; filename=\"".$attachment_filename."\"\r\n\r\n";
	$mail .= $content."\r\n";
	$mail .= "--------------{$uid}--\r\n";

	if (mail($mail_to, $subject, $mail, $header)) {
		return true;
	}
	return false;
}
