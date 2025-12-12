<?php

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", "your_password_here");

//JSON DNS zone file location
define("PATH_TO_THE_JSON_FILE", "/path/to/json/file.json");

//DNS zone name
define("DNS_ZONE_NAME", "domain.com");

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";
	
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/geodns-zones-import-aws)');
	
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

$content = file_get_contents(PATH_TO_THE_JSON_FILE);
$array = json_decode($content, true);

//DNS zone add
$response = apiCall('dns/register.json', "domain-name=".DNS_ZONE_NAME."&zone-type=geodns");
if($response['status'] == "Success") {
  echo "GeoDNS zone ".DNS_ZONE_NAME." successfully created.\n";
}

//Access the array
foreach ($array as $row) {
  foreach ($row as $element) {
    foreach ($element['ResourceRecords'] as $resourceRecords) {
      //Remove DOMAIN.TLD.
      $subject = preg_replace("/".DNS_ZONE_NAME.".$/s", '', $element['Name']);
      $checkForDot = substr($subject, -1);
      if($checkForDot == '.') {
        $subject = substr($subject, 0, -1);
      }
      //FIX UNSUPPORTED TTL VALUES
      if ($element['TTL'] <= 60) {
         $element['TTL'] = 60;
      } elseif ($element['TTL'] == 600){
         $element['TTL'] = 900;
      }

      //BEGIN RECORDS IMPORT
      switch ($element['Type']):
        case 'MX':
          preg_match('#^([0-9]+)\s+(.+)$#i', $resourceRecords['Value'], $mxSubject);
          $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$mxSubject[2]}&ttl={$element['TTL']}&priority={$mxSubject[1]}");
          if($response['status'] == "Success") {
            echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$mxSubject[2]."\n";
            $count++;
          }
        break;
        case 'NS':
          if ($subject == "") {
            echo "NS record for root domain (".DNS_ZONE_NAME.") pointed to ".$resourceRecords['Value']." was not imported. Your DNS zone is created with the default available GeoDNS servers.\n";
            }
          else {
            $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}");
            if($response['status'] == "Success") {
              echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']."\n";
            $count++;
            }
          } 
        break;
        case 'SPF':  
        case 'TXT':
        case 'CNAME':
        case 'AAAA':  
        case 'A':        
          if (isset($element['GeoLocation']['CountryCode'])) {
            $geolocations = apiCall('dns/get-geodns-locations.json', "domain-name=".DNS_ZONE_NAME."");
            if ($element['GeoLocation']['CountryCode'] == '*') {
                $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}");
                if($response['status'] == "Success") {
                  echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']."\n";
                  $count++;
                }
            } else {
              foreach($geolocations as $geolocation) {
                if ($geolocation['code'] == $element['GeoLocation']['CountryCode']) {
                  $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}&geodns-location={$geolocation['id']}");
                  if($response['status'] == "Success") {
                    echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']." with location ".$geolocation['name']."\n";
                    $count++;
                  }
                }
              }
            }
          } else if (isset($element['GeoLocation']['ContinentCode'])) {
            switch ($element['GeoLocation']['ContinentCode']):
              case 'AF':
                $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}&geodns-location=2");
                if($response['status'] == "Success") {
                  echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']." with location Africa.\n";
                  $count++;
                }
                break;
              case 'AN':
                echo "Records for Antarctica cannot be added. Please contact Technical Support.\n";
                break;
              case 'AS':
                $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}&geodns-location=4");
                if($response['status'] == "Success") {
                  echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']." with location Asia.\n";
                  $count++;
                }                
                break;
              case 'EU':
                $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}&geodns-location=5");
                if($response['status'] == "Success") {
                  echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']." with location Europe.\n";
                  $count++;
                }
                break;
              case 'NA':
                $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}&geodns-location=6");
                if($response['status'] == "Success") {
                  echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']." with location North America.\n";
                  $count++;
                }
                break;
              case 'OC':
                $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}&geodns-location=7");
                if($response['status'] == "Success") {
                  echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']." with location Oceania.\n";
                  $count++;
                }
                break;
              case 'SA':
                $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}&geodns-location=8");
                if($response['status'] == "Success") {
                  echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']." with location South America.\n";
                  $count++;
                }
                break;
            endswitch;
          } else {
            $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$resourceRecords['Value']}&ttl={$element['TTL']}");
            if($response['status'] == "Success") {
              echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']."\n";
              $count++;
            }
          }
        break;
      case 'SOA':
        echo $element['Type']." cannot be imported. Please edit the SOA settings from your DNS zone management page.\n";
      break;
      //SRV IMPORT IS EXPERIMENTAL, REMOVE COMMENTS TO TEST
     /*case 'SRV':
       preg_match('#^([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.+)$#i', $resourceRecords['Value'], $srvSubject);
       $response = apiCall('dns/add-record.json', "domain-name=".DNS_ZONE_NAME."&record-type={$element['Type']}&host={$subject}&record={$srvSubject[4]}&ttl={$element['TTL']}&priority={$srvSubject[1]}&weight={$srvSubject[2]}&port={$srvSubject[3]}");
      if($response['status'] == "Success") {
        echo "Imported ".$element['Type']." record for ".$element['Name']." pointed to ".$resourceRecords['Value']."\n";
        $count++;
      
      break;*/ 
      default :
        echo "Unknow record type ".$element['Type']." for host ".$subject.". Please contact Technical Support at https://www.cloudns.net/support.\n";
      break;
      endswitch;
    }
  }
}
echo $count." records were imported to GeoDNS zone ".DNS_ZONE_NAME."";
?>

