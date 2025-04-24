<?php

// === ClouDNS Authentication Credentials ===
// Replace XXX with your actual ClouDNS API ID and Password
define("AUTH_ID", XXX);
define("AUTH_PASS", "XXX");

// IP address of your primary DNS server
define("MASTER_IP", "xxx.xxx.xxx.xxx");

// Optional second master server IP (uncomment and set if needed)
// define("MASTER_IP2", "xxx.xxx.xxx.xxx");

// Directory where your BIND .db zone files are located
define("ZONES_DIR", "/var/named/");

// === Compatibility Helper ===
// PHP < 8.0 doesn't have str_ends_with(), so we define it if missing
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

// === ClouDNS API Request Function ===
// This function performs a POST request to the ClouDNS API
function apiCall($url, $data) {
    $url = "https://api.cloudns.net/{$url}";
    $data = "auth-id=" . AUTH_ID . "&auth-password=" . AUTH_PASS . "&{$data}";

    $init = curl_init();
    curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($init, CURLOPT_URL, $url);
    curl_setopt($init, CURLOPT_POST, true);
    curl_setopt($init, CURLOPT_POSTFIELDS, $data);
    curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1');
    $content = curl_exec($init);
    curl_close($init);

    return json_decode($content, true);
}

// === Step 1: Authenticate with ClouDNS ===
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] === 'Failed') {
    die("Login failed: " . $login['statusDescription'] . "\n");
}

// === Step 2: Load .db Files from Zone Directory ===
// This filters out all non-.db files, then sorts the list alphabetically
$zoneFiles = array_filter(scandir(ZONES_DIR), fn($f) => str_ends_with($f, '.db'));
sort($zoneFiles);

// === Initialize Counters ===
$added = 0;          // Zones successfully added
$failed = 0;         // Zones that failed due to other errors
$limit_reached = 0;  // Zones that failed specifically due to "Zone limit reached"

echo "== Starting zone sync from " . ZONES_DIR . " to ClouDNS ==\n";

// === Step 3: Process Each Zone File ===
foreach ($zoneFiles as $zoneFile) {
    echo "Processing: {$zoneFile}... ";

    // Strip ".db" extension to get the domain name
    $zoneShort = preg_replace('/\.db$/', '', $zoneFile);

    // Attempt to register the zone as a slave
    echo "registering '{$zoneShort}' as slave... ";
    $response = apiCall('dns/register.json', "domain-name={$zoneShort}&zone-type=slave&master-ip=" . MASTER_IP);

    // Handle failed API response
    if (isset($response['status']) && $response['status'] === 'Failed') {
        $desc = $response['statusDescription'] ?? 'Unknown error';
        echo "FAILED: {$desc}\n";

        // Check for specific error: "Zone limit reached"
        if (stripos($desc, 'Zone limit') !== false) {
            $limit_reached++;
        } else {
            $failed++;
        }

        continue; // Skip to next file
    }

    // If registration succeeded
    echo "success.\n";
    $added++;

    // Optional: Add second master IP if defined
    if (defined('MASTER_IP2') && constant('MASTER_IP2')) {
        echo " -> Adding second master IP: " . MASTER_IP2 . "\n";
        apiCall('dns/add-master-server.json', "domain-name={$zoneShort}&master-ip=" . MASTER_IP2);
    }
}

// === Final Summary ===
echo "\n== Sync Summary ==\n";
echo "Zones added:        {$added}\n";
echo "Failures:           {$failed}\n";
echo "Zone limit reached: {$limit_reached}\n";
