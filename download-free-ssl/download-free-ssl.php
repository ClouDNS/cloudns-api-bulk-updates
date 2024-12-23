<?php

// This script downloads the Free SSL certificate for a predefined DNS zone
// and saves the CRT and private key to files in the same directory

// Configure the API credentials and domain name

$auth_id = 'XXX';
$auth_pass = 'XXX';
$domain_name = 'example.com';

// Set the API URL with predefined variables
$api_url = "https://api.cloudns.net/dns/freessl-get.json?sub-auth-id={$auth_id}&auth-password={$auth_pass}&domain-name={$domain_name}";

// Fetch the JSON response from the API
$response = file_get_contents($api_url);

if ($response === false) {
    die("Error fetching API data.\n");
}

// Decode the API response into an associative array
$data = json_decode($response, true);

if ($data === null) {
    die("Error decoding API response.\n");
}

// Extract the private key and certificate
$private_key = isset($data['key']) ? $data['key'] : '';
$certificate = isset($data['fullchain']) ? $data['fullchain'] : '';

if (empty($private_key) || empty($certificate)) {
    die("Error: Private key or certificate not found.\n");
}

// Save the private key and certificate to files
file_put_contents("{$domain_name}.key", $private_key);
file_put_contents("{$domain_name}.crt", $certificate);

// Output a success message
echo "SSL certificate and private key successfully saved to {$domain_name}.crt and {$domain_name}.key.\n";
