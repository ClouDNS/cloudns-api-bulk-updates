<?php

function apiCall ($url, $data) {
	$url = 'http://api.dev.cloudns.net/'. $url;
	$data = '&auth-id=AUTHID&auth-password=AUTHPASSWORD&'. $data;

	echo "Request:\n{$data}\n\n";

	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);

	$content = curl_exec($init);
	curl_close($init);
	
	echo "Response:\n{$content}\n\n";

	$xml = new SimpleXMLElement($content);

	// some methods are returning Status and StatusDescription tags
	if ($xml->status) {
		var_dump($xml);
	// other are returning either item with name attribute tags or their own specific tags
	} else {
		$item = array();
		foreach ($xml as $object) {
			if ($object['name']) {
				$name = (int)$object['name'];
				$item[$name] = $object;
				//		^-cast to string or int, if needed
			} else {
				$item = $xml;
			}
		}
		var_dump($item);
	}
}

// register new master domain
// var_dump(apiCall('dns/register.xml', 'domain-name=domain.com&zone-type=master'));

// register new slave domain with ipv4 server
// var_dump(apiCall('dns/register.xml', 'domain-name=domain.com&zone-type=slave&master-ip=1.2.3.4'));

// register new slave domain with ipv6 server
// var_dump(apiCall('dns/register.xml', 'domain-name=domain.com&zone-type=slave&master-ip=1:1:1:2:2'));

// delete domain
// var_dump(apiCall('dns/delete.xml', 'domain-name=domain.com'));

// list records
// var_dump(apiCall('dns/records.xml', 'domain-name=domain.com'));

// delete record
// var_dump(apiCall('dns/delete-record.xml', 'domain-name=domain.com&record-id=1099148'));

// add new A record
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=A&host=test1&record=10.10.10.10&ttl=300'));

// add new AAAA record
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=AAAA&host=aaaa&record=1:1:1:1:1&ttl=60'));

// add new MX record
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=MX&host=&record=mailforward2.cloudns.net&ttl=86400&priority=20'));

// add new CNAME record
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=CNAME&host=cname&record=cloudns.net&ttl=3600'));

// add new TXT record
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=TXT&host=&record=spf1 ip4:95.211.130.15&ttl=3600'));

// add new NS record
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=NS&host=&record=pns2.cloudns.net&ttl=3600'));

// add new SRV record
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=SRV&host=_xmpp-server._tcp&record=test.com&ttl=3600&priority=11&weight=22&port=33'));

// add new web redirect by default without frame and redirect type 302
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600'));

// add new web redirect with redirect type 301
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600&redirect-type=301'));

// add new web redirect with redirect type 301 and save path
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600&redirect-type=301&save-path=1'));

// add new web redirect with frame
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600&frame=1&frame-title=title&frame-description=description&frame-keywords=keywords'));

// add new web redirect with frame and save path
// var_dump(apiCall('dns/add-record.xml', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600&frame=1&frame-title=title&frame-description=description&frame-keywords=keywords&save-path=1'));

// add new RP record
// var_dump(apiCall('dns/add-record.xml', 'domain-name=test-zone.com&record-type=RP&host=test&ttl=3600&mail=test@mail.com&txt=cloudns.net'));

// modify A record
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1101252&host=a&record=20.20.20.20&ttl=3600'));

// modify AAAA record
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1101253&host=aaaaaaaaa&record=20:20:20:20:20&ttl=3600'));

// modify MX record
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1101254&host=&record=mailforward2.cloudns.net&ttl=86400&priority=20'));

// modify CNAME record
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1101255&host=testcname&record=www.cloudns.net&ttl=60'));

// modify TXT record
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1101256&host=txt&record=spf1 ip4:1.1.1.1&ttl=60'));

// modify NS record
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1101257&host=1&record=ns1.cloudns.net&ttl=60'));

// modify SRV record
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1101258&host=_xmpp-test._tcp&record=cloudns.net&ttl=60&priority=10&weight=20&port=30'));

// modify web redirect - without frame, without save path and redirect type 301
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1099296&host=wr&record=http://www.cloudns.net&ttl=60&frame=0&save-path=0&redirect-type=301'));

// modify web redirect - without frame, with save path and redirect type 301
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1099296&host=wr&record=http://www.cloudns.net&ttl=3600&frame=0&save-path=1&redirect-type=301'));

// modify web redirect - with frame
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=domain.com&record-id=1099296&host=wr&record=http://www.cloudns.net&ttl=3600&frame=1&frame-title=t&frame-keywords=k&frame-description=d'));

// modify RP record
// var_dump(apiCall('dns/mod-record.xml', 'domain-name=test-zone.com&record-id=1495015&host=qwe12312331232123&ttl=3600&mail=my@mail.com&txt=txt.domain.com'));

// list master servers of slave zone
// var_dump(apiCall('dns/master-servers.xml', 'domain-name=domain.com'));

// add master ip v4 to slave zone
// var_dump(apiCall('dns/add-master-server.xml', 'domain-name=domain.com&master-ip=10.10.10.1'));

// add master ip v6 to slave zone
// var_dump(apiCall('dns/add-master-server.xml', 'domain-name=domain.com&master-ip=2:2:2:2:2'));

// del master server
// var_dump(apiCall('dns/delete-master-server.xml', 'domain-name=domain.com&master-id=1099228'));

// soa details
// var_dump(apiCall('dns/soa-details.xml', 'domain-name=domain.com'));

// modify soa details
// var_dump(apiCall('dns/modify-soa.xml', 'domain-name=domain.com&primary-ns=ns2.cloudns.net&admin-mail=test@cloudns.net&refresh=3000&retry=3000&expire=2000000&default-ttl=3000'));

// list mail forwards
// var_dump(apiCall('dns/mail-forwards.xml', 'domain-name=domain.com'));

// add mail forward
// var_dump(apiCall('dns/add-mail-forward.xml', 'domain-name=domain.com&box=&host=&destination=test@abv.bg'));
// var_dump(apiCall('dns/add-mail-forward.xml', 'domain-name=domain.com&box=support&host=test&destination=test@abv.bg'));

// delete mail forward
// var_dump(apiCall('dns/delete-mail-forward.xml', 'domain-name=domain.com&mail-forward-id=3558'));

// domain update status
// var_dump(apiCall('dns/update-status.xml', 'domain-name=domain.com'));

// domain is updated
// var_dump(apiCall('dns/is-updated.xml', 'domain-name=domain.com'));


// hourly statistics
// var_dump(apiCall('dns/statistics-hourly.xml', 'domain-name=domain.com&year=2012&month=12&day=02'));

// daily statistics
// var_dump(apiCall('dns/statistics-daily.xml', 'domain-name=domain.com&year=2012&month=10'));

// monthly statistics
// var_dump(apiCall('dns/statistics-monthly.xml', 'domain-name=domain.com&year=2011'));

// yearly statistics
// var_dump(apiCall('dns/statistics-yearly.xml', 'domain-name=domain.com'));

// last 30 days statistics
// var_dump(apiCall('dns/statistics-last-30-days.xml', 'domain-name=domain.com'));

// add cloud/bulk domain
// var_dump(apiCall('dns/add-cloud-domain.xml', 'domain-name=bulk-master.com&cloud-domain-name=bulk-domain.net'));

// list cloud/bulk domains
// var_dump(apiCall('dns/list-cloud-domains.xml', 'domain-name=bulk-master.com'));
 
// set master of cloud/bulk domains
// var_dump(apiCall('dns/set-master-cloud-domain.xml', 'domain-name=bulk-domain.net'));

// delete cloud/bulk domain
// var_dump(apiCall('dns/delete-cloud-domain.xml', 'domain-name=bulk-domain.net'));

// list of axfr per domain
// var_dump(apiCall('dns/axfr-list.xml', 'domain-name=domain.com'));

// adds new axfr record
// var_dump(apiCall('dns/axfr-add.xml', 'domain-name=domain.com&ip=1.2.3.6'));

// deletes axfr record
// var_dump(apiCall('dns/axfr-remove.xml', 'domain-name=domain.com&id=15077'));

// get pages count
// var_dump(apiCall('dns/get-pages-count.xml', 'rows-per-page=10&search=domain'));

// list all zones (pagination)
// var_dump(apiCall('dns/list-zones.xml', 'page=1&rows-per-page=10&search=domain'));

// get zone count and user type limit
// var_dump(apiCall('dns/get-zones-stats.xml', ''));

 // copy records from zone
// var_dump(apiCall('dns/copy-records.xml', 'domain-name=domain.com&from-domain=from-domain.com&delete-current-records=1'));

// get Dynamic URL of a record
// var_dump(apiCall('dns/get-dynamic-url.xml', 'domain-name=domain.com&record-id=1235813'));

// import records via transfer
// var_dump(apiCall('dns/axfr-import.json', 'domain-name=abligator.de&server=85.25.34.84'));
 
/* *** reseller module *** */

// checks for domain availability
// var_dump(apiCall('domains/check-available.xml', 'name=domain&tld[]=net&tld[]=com'));

