<?php


$errorcondition = 0;

// Auth ID and Password
define("AUTH_ID", XXXX);
define("AUTH_PASS", "XXXX");

//This script woks on the basis that:
//We want to see only OLD_MASTER_IP.
//We do not want to see NEW_MASTER_IP Listed
//OLD_MASTER_IP is the IP of your old Plesk serever 
//NEW_MASTER_IP is the IP of your new Plesk server

// IP address of the master servers we are looking for
define("OLD_MASTER_IP", "4.3.3.1");
define("NEW_MASTER_IP", "1.2.3.5");

// DNS Zone File location
define("ZONES_DIR", "/var/named/chroot/var");


// function to connect to the API
function apiCall($url, $data) {
    $url = "https://api.cloudns.net/{$url}";
    $data = "auth-id=" . AUTH_ID . "&auth-password=" . AUTH_PASS . "&{$data}";
    $init = curl_init();
    curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($init, CURLOPT_URL, $url);
    curl_setopt($init, CURLOPT_POST, true);
    curl_setopt($init, CURLOPT_POSTFIELDS, $data);
    curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/plesk-zones-check-master)');
    $content = curl_exec($init);
    if (PHP_VERSION_ID < 80000) {
        curl_close($init);
    }
    return json_decode($content, true);
}

// Check if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
    die($login['statusDescription']);
}

$invalid_zones=array();

// gets the zone files names by reading what named has in its directory
if ($handle = opendir(ZONES_DIR)) {
    // loops through the files
    while (false !== ($zoneName = readdir($handle))) {

        //DEFINE error condidion default (0)
        $errorcondition = 0;

// IMPORTANT! In the line below, you will see an entry "2.3.4.in-addr.arpa". This will be different for you and you should change it to match the subnet your current server is on. e.g. if your IP is 88.66.55.44 then your file would be 55.66.88.in-addr.arpa and you would change the name as appropriate in the line below. It is not terrribly important but it will throw up an error which might distract you if you don't change it.
        // skips the parent dirs
        if ($zoneName != "." && $zoneName != ".." && $zoneName != "run" && $zoneName != "named.root" && $zoneName != "make-localhost" && $zoneName != "localhost.rev" && $zoneName != "PROTO.localhost.rev" && $zoneName != "2.3.4.in-addr.arpa" && $zoneName != "localhost.rev.saved_by_psa") {


            // checks the zone name is not in the invalid_zones list
            if (!in_array($zoneName, $invalid_zones)) {

                print ("\n\n**** Processing: $zoneName ****\n");
                $dothis = true;
                if ($dothis === true) {
                    //calling the api to find all master IDs for debugging
                    $response = apiCall('dns/master-servers.json', "domain-name={$zoneName}");
                    // if the api returns the zone is invalid we put it in the file with the invalid zones (NOT NEEDED IN THIS SCRIPT)
                    if (isset($response['status']) && ( $response['status'] == 'Failed' )) {
                        //file_put_contents(TMPFILE, $zoneName."\n", FILE_APPEND);
                        print ("ERROR: Could not list Master IDs or IPs for $zoneName >> ");
                        print($response["status"]);
                        print (" >> ");
                        print($response["statusDescription"]);
                        print (" \n");
                    } else {
                        $numKeys = count($response);
                        print ("There are $numKeys Master IPs for $zoneName : \n");

                        //DEBUG print the IPs associated with each key
                        foreach ($response as $key => $value) {
                            print ("ID: $key; IP: $value \n");
                        }
                       //We want to see OLD_MASTER. We do not want to see NEW_MASTER.
                        //Test is edited accordingly.
                        //Find key (Master ID) based on value (NEW_MASTER_IP)
                        $newMasterID = array_search(NEW_MASTER_IP, $response, 0);

                        if (!isset($newMasterID) OR $newMasterID == "") {
                           
                        } else {
                            //Oh oh! We found NEW_MASTER! We don't want to see it. Error! 
                            print ("!! ERROR: NEW_MASTER_IP found! ID = $newMasterID !!");
                            print (" \n");
                        } 
                        //Find key (Master ID) based on value (OLD_MASTER_IP)
                        $oldMasterID = array_search(OLD_MASTER_IP, $response, 0);

                        if (!isset($oldMasterID) OR $oldMasterID == "") {
                            print ("!! ERROR -- CANNOT FIND OLD_MASTER_IP !! \n");
                        } else {
                            print ("OK: ID = $oldMasterID has been allocated to ");
                            print OLD_MASTER_IP;
                            print (" \n");
                        } //else
                    } //else
                }
            } // end valid zone processing
        } // end invalid checking
    } // zone listing

    closedir($handle);
}

?>
