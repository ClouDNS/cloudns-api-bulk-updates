<?php

// Adds a PTR Record to each address in an IPv4 /24 Reverse Zone hosted with ClouDNS
// ie. A placeholder or prefix-wide record to be added across the entire zone
// 
// Docs, list records: https://www.cloudns.net/wiki/article/57/
// Docs, add record: https://www.cloudns.net/wiki/article/58/
// 
// Easily adapted to your needs, simply update the code
// The Network Crew Pty Ltd (TNC) & Co. (Australia)

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Auth ID and Password
define("AUTH_ID", XXXX);
define("AUTH_PASS", "xxx");

// Utility function for API calls
function apiCall($url, $data) {
    $fullUrl = "https://api.cloudns.net/{$url}";
    // Append authentication data to the input data
    $postData = "auth-id=" . AUTH_ID . "&auth-password=" . AUTH_PASS;
    if ($data) {
        $postData .= "&" . $data;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates)');
    $content = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch));
    }

    curl_close($ch);
    $decodedResponse = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error_msg());
    }

    return $decodedResponse;
}

// Function to manage PTR records in a /24 subnet
function manageZoneRecords($baseDomain) {
    $existingRecords = apiCall('dns/records.json', "domain-name={$baseDomain}&type=PTR");

    if (!is_array($existingRecords) || empty($existingRecords)) {
        die("Error fetching records or no records found: " . json_encode($existingRecords));
    }

    // Extracting the 'host' values from the records
    $existingHosts = array_column($existingRecords, 'host');

    for ($i = 1; $i <= 254; $i++) {
        $host = (string)$i;
        // Define a new line variable based on the environment
        $newLine = (php_sapi_name() == "cli") ? "\n" : "<br>";

        // Check if the current host is already in the array of existing hosts
        if (!in_array($host, $existingHosts)) {
            $record = 'PLACEHOLDER.RECORD.local';
            $result = apiCall('dns/add-record.json', "domain-name={$baseDomain}&record-type=PTR&host={$host}&record={$record}&ttl=3600");
            if (isset($result['status']) && $result['status'] == 'Success') {
                echo "Added PTR record for $host in $baseDomain\n" . $newLine;
            } else {
                echo "Failed to add PTR record for $host in $baseDomain: " . json_encode($result) . "\n" . $newLine;
            }
        } else {
            echo "Record already exists for $host in $baseDomain. No new record added.\n" . $newLine;
        }
    }

    echo "Done!";
}

// Example usage: replace 'CCC.BBB.AAA.in-addr.arpa' with your specific zone
manageZoneRecords('CCC.BBB.AAA.in-addr.arpa');

?>
