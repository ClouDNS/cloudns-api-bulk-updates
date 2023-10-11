<?php

$errorcondition = 0;

// Auth ID and Password
define("AUTH_ID", xxxx);
define("AUTH_PASS", "xxxx");

//OLD_MASTER_IP is the IP of your old Plesk server 
//NEW_MASTER_IP is the IP of your new Plesk server

// IP address of the master servers we are looking for
define("OLD_MASTER_IP", "1.2.3.5");
define("NEW_MASTER_IP", "4.3.3.1");

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
    curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/plesk-change-master-server-ip)');
    $content = curl_exec($init);
    curl_close($init);
    return json_decode($content, true);
}

// Check if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
    die($login['statusDescription']);
}

$invalid_zones=array();
// get the zone files names
if ($handle = opendir(ZONES_DIR)) {
    // loops through the files
    while (false !== ($zoneName = readdir($handle))) {

        //DEFINE error condidion default (0)
        $errorcondition = 0;

// IMPORTANT! In the line below, you will see an entry "2.3.4.in-addr.arpa". This will be different for you and you should change it to match the subnet your current server is on. e.g. if your IP is 88.66.55.44 then your file would be 55.66.88.in-addr.arpa (reversed IP, only first three parts) and you would change the name as appropriate in the line below. It is not terrribly important but it will throw up an error which might distract you if you don't change it.
// skips the parent dirs
        var_dump($zoneName);
        if ($zoneName != "." && $zoneName != ".." && $zoneName != "run" && $zoneName != "named.root" && $zoneName != "make-localhost" && $zoneName != "localhost.rev" && $zoneName != "PROTO.localhost.rev" && $zoneName != "2.3.4.in-addr.arpa" && $zoneName != "localhost.rev.saved_by_psa") {

            // checks the zone name is not in the invalid_zones list
            if (!in_array($zoneName, $invalid_zones)) {
                print ("\n\n**** Processing: $zoneName ****\n");
                print ("OLD_MASTER_IP = ");
                print OLD_MASTER_IP;
                print (" :::  NEW_MASTER_IP = ");
                print NEW_MASTER_IP;
                print (" \n");

                //Find ALL Master IPs and get the ID for OLD_MASTER_IP
                //calling the api to find master ID s
                $response = apiCall('dns/master-servers.json', "domain-name={$zoneName}");
                // if the api returns the zone is invalid we put it in the file with the invalid zones (not actually done in this script)
                if (isset($response['status']) && ( $response['status'] == 'Failed' )) {
                    //file_put_contents(TMPFILE, $zoneName."\n", FILE_APPEND);
                    print ("ERROR: Could not list Master IDs or IPs for $zoneName >> ");
                    print($response["status"]);
                    print (" >> ");
                    print($response["statusDescription"]);
                    print (" \n");
                    $errorcondition = 1; //no point trying to add or remove etc
                } else {

                    //Find key (Master ID) based on value (OLD_IP)
                    $oldMasterID = array_search(OLD_MASTER_IP, $response, 0);

                    //SANITY CHECK - did we get a match for OLD_MASTER_IP ?
                    if (!isset($oldMasterID) OR $oldMasterID == "") {
                        //No match. Something bad happened.
                        $errorcondition = 1;
                        print ("!! FATAL ERROR - Could not find OLD_MASTER_IP defined for $zoneName !! \n");
                    } //if error
                    else {
                        print ("Found ID = $oldMasterID ");
                        print ("for OLD_MASTER_IP = ");
                        print OLD_MASTER_IP;
                        print (" \n");
                    } //else no error
                } //else no error main loop
//debug adding new master
                $dothis = true;
                if ($dothis === true) {

                    if ($errorcondition != 1) {

                        // Add New master IP NEW_MASTER_IP to all zones
                        //calling the api
                        $response = apiCall('dns/add-master-server.json', "domain-name={$zoneName}&master-ip=" . NEW_MASTER_IP);
                        // if the api returns the zone is invalid we put it in the file with the invalid zones (not done in this script)
                        if (isset($response['status']) && ( $response['status'] == 'Failed' )) {
                            //file_put_contents(TMPFILE, $zoneName."\n", FILE_APPEND);
                            print ("ERROR: Cannot add new Master IP to $zoneName >> ");
                            print($response["status"]);
                            print (" >> ");
                            print($response["statusDescription"]);
                            print (" \n");
                        } else {
                            print("ADDED: NEW_MASTER_IP \n");
                        }
                    } //errorcondition
                    else {
                        Print("Errorcondition: Can't add new_master_IP. No oldMasterID for $zoneName \n");
                    }
                }
                $dothis = true;
                if ($dothis === true) {

                    // DELETE OLD_MASTER_IP by ID

                    if ($errorcondition != 1) {


                        //calling the api
                        $response = apiCall('dns/delete-master-server.json', "domain-name={$zoneName}&master-id={$oldMasterID}");
                        // if the api returns the zone is invalid we put it in the file with the invalid zones
                        if (isset($response['status']) && ( $response['status'] == 'Failed' )) {
                            //file_put_contents(TMPFILE, $zoneName."\n", FILE_APPEND);
                            print ("ERROR: Cannot delete old master IP for $zoneName >> ");
                            print($response["status"]);
                            print (" >> ");
                            print($response["statusDescription"]);
                            print (" \n");
                        } else {
                            print("DELETED: OLD_MASTER_IP \n");
                        }
                    } //errorcondition
                    else {
                        print("Errorcondition: Can't delete old_master_ip. No oldMasterID for $zoneName \n");
                    }
                }



// debug printing all masters
//$dothis=true is my STUPID WAY OF BEING ABLE TO ISOLATE SECTIONS NOT NEEDED OR WANTED WHEN DEBUGGING
//THis section is optional really. It just lists the master IPs found as a sanity check.
//At this point the Master IP should have been changed to the new one.
                $dothis = true;
                if ($dothis === true) {



                    //calling the api to find all master IDs for debugging
                    $response = apiCall('dns/master-servers.json', "domain-name={$zoneName}");
                    // if the api returns the zone is invalid we put it in the file with the invalid zones
                    if (isset($response['status']) && ( $response['status'] == 'Failed' )) {
                        //file_put_contents(TMPFILE, $zoneName."\n", FILE_APPEND);
                        print ("ERROR: Could not list Master IDs or IPs for $zoneName >> ");
                        print($response["status"]);
                        print (" >> ");
                        print($response["statusDescription"]);
                        print (" \n");
                    } else {

                        //DEBUG
                        //print("IP/ID Array for $zoneName \n"); print_r($response);
                        //DEBUG count keys
                        $numKeys = count($response);
                        print ("There are $numKeys Master IPs for $zoneName : \n");

                        //DEBUG
                        foreach ($response as $key => $value) {
                            print ("ID: $key; IP: $value \n");
                        }



                        //Find key (Master ID) based on value (NEW_MASTER_IP)
                        $newMasterID = array_search(NEW_MASTER_IP, $response, 0);

                        if (!isset($newMasterID) OR $newMasterID == "") {
                            print ("!! FATAL ERROR -- CANNOT FIND NEW_MASTER_IP LISTED !! \n");
                        } else {
                            print ("SUCCESS: ID = $newMasterID has been allocated to ");
                            print NEW_MASTER_IP;
                            print (" \n");
                        } //else not fatal
                    } //else
                }
            } // end valid zone processing
        } // end invalid checking
    } // zone listing

    closedir($handle);
}
?>
