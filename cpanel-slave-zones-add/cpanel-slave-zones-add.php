<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "xxx");

// IP address of the master server (primary server)
define("MASTER_IP", "xxx.xxx.xxx.xxx");

// Second IP address for master server (it may be IPv6 or IPv4 address)
//define("MASTER_IP2", "xxx.xxx.xxx.xxx");

// the directory with the zone files, their names are used to create the slave zones, not the content of the files
define("ZONES_DIR", "/var/named/");

// this file will contain a list of files that are not dns zone files and there won't be a request to be added the next time the script runs
define("TMPFILE", "/tmp/cloudns_invalid-zone-names.txt");

if ((file_exists(TMPFILE) && !is_writable(TMPFILE)) || (!file_exists(TMPFILE) && !is_writable(dirname(TMPFILE)))) {
        die("TMPFILE (".TMPFILE.") is not writable. Please make it writable to continue or update the config option to a new path.");
}

// Polyfill for PHP < 8.0
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return substr($haystack, -strlen($needle)) === $needle;
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
        curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/cpanel-slave-zones-add)');
        $content = curl_exec($init);
        curl_close($init);
        return json_decode($content, true);
}

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
        die($login['statusDescription']);
}

// Load invalid zone names
$invalid_zones = file_exists(TMPFILE)
    ? file(TMPFILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
    : [];

$added = 0;
$skipped = 0;
$failed = 0;

echo "== Starting zone sync from " . ZONES_DIR . " to ClouDNS ==\n";

$handle = opendir(ZONES_DIR);
if ($handle) {
    while (false !== ($zoneName = readdir($handle))) {
        if ($zoneName === '.' || $zoneName === '..') continue;

        echo "Processing: {$zoneName}... ";

        if (in_array($zoneName, $invalid_zones)) {
            echo "skipped (previously invalid).\n";
            $skipped++;
            continue;
        }

        if (!str_ends_with($zoneName, '.db')) {
            echo "not a zone file. Marking as invalid.\n";
            file_put_contents(TMPFILE, $zoneName . "\n", FILE_APPEND);
            $skipped++;
            continue;
        }

        $zoneShort = preg_replace('/\.db$/', '', $zoneName);
        echo "registering '{$zoneShort}' as slave... ";

        $response = apiCall('dns/register.json', "domain-name={$zoneShort}&zone-type=slave&master-ip=" . MASTER_IP);

        if (isset($response['status']) && $response['status'] === 'Failed') {
            echo "FAILED: {$response['statusDescription']}\n";
            file_put_contents(TMPFILE, $zoneName . "\n", FILE_APPEND);
            $failed++;
            continue;
        }

        echo "success.\n";
        $added++;

        if (defined('MASTER_IP2') && constant('MASTER_IP2')) {
            echo " -> Adding second master IP: " . MASTER_IP2 . "\n";
            apiCall('dns/add-master-server.json', "domain-name={$zoneShort}&master-ip=" . MASTER_IP2);
        }
    }

    closedir($handle);

    echo "\n== Sync Summary ==\n";
    echo "Zones added:  {$added}\n";
    echo "Skipped:      {$skipped}\n";
    echo "Failures:     {$failed}\n";
} else {
    echo "Error: Unable to open zone directory at " . ZONES_DIR . "\n";
}
