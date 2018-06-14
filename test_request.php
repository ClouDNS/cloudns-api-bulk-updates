<?php

function apiCall ($url, $data) {
	$url = 'https://api.cloudns.net/'. $url;
	$data = '&auth-id=AUTHID&auth-password=AUTHPASSWORD&'. $data;

	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);

	$content = curl_exec($init);

	//var_dump(curl_getinfo($init));
	//echo $content;

	curl_close($init);

	return json_decode($content, true);
}

// list zones
//var_dump(apiCall('dns/list-zones.json', 'page=1&rows-per-page=10'));

// register new master domain
// var_dump(apiCall('dns/register.json', 'domain-name=domain.com&zone-type=master'));

// register new parked zone
// var_dump(apiCall('dns/register.json', 'domain-name=domain.com&zone-type=parked'));

// register new slave domain with ipv4 server
// var_dump(apiCall('dns/register.json', 'domain-name=domain.com&zone-type=slave&master-ip=1.2.3.4'));

// register new slave domain with ipv6 server
// var_dump(apiCall('dns/register.json', 'domain-name=domain.com&zone-type=slave&master-ip=1:1:1:2:2'));

// register new ipv4 reverse zone
// var_dump(apiCall('dns/register.json', 'domain-name=4.3.2.1.in-addr.arpa&zone-type=master'));

// delete domain
// var_dump(apiCall('dns/delete.json', 'domain-name=domain.com'));

// list records
// var_dump(apiCall('dns/records.json', 'domain-name=domain.com'));

// delete record
// var_dump(apiCall('dns/delete-record.json', 'domain-name=domain.com&record-id=1099148'));

// add new A record
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=A&host=test1&record=10.10.10.10&ttl=300'));

// add new AAAA record
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=AAAA&host=aaaa&record=1:1:1:1:1&ttl=60'));

// add new MX record
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=MX&host=&record=mailforward2.cloudns.net&ttl=86400&priority=20'));

// add new CNAME record
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=CNAME&host=cname&record=cloudns.net&ttl=3600'));

// add new TXT record
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=TXT&host=&record=spf1 ip4:95.211.130.15&ttl=3600'));

// add new NS record
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=NS&host=&record=pns2.cloudns.net&ttl=3600'));

// add new SRV record
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=SRV&host=_xmpp-server._tcp&record=test.com&ttl=3600&priority=11&weight=22&port=33'));

// add new web redirect by default without frame and redirect type 302
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600'));

// add new web redirect with redirect type 301
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600&redirect-type=301'));

// add new web redirect with redirect type 301 and save path
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600&redirect-type=301&save-path=1'));

// add new web redirect with frame
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600&frame=1&frame-title=title&frame-description=description&frame-keywords=keywords'));

// add new web redirect with frame and save path
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=WR&host=&record=http://cloudns.net&ttl=3600&frame=1&frame-title=title&frame-description=description&frame-keywords=keywords&save-path=1'));

// add new RP record
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=RP&host=test&ttl=3600&mail=test@mail.com&txt=cloudns.net'));
// add new PTR record
// var_dump(apiCall('dns/add-record.json', 'domain-name=3.2.1.in-addr.arpa&record-type=PTR&host=1&ttl=3600&record=ptr.example.com'));

// add new SSHFP record
// var_dump(apiCall('dns/add-record.json', 'domain-name=domain.com&record-type=SSHFP&host=apitest&record=9fd1935a5739a39fe6c79f2754076880c7d79bd2&ttl=300&algorithm=2&fptype=1'));

// add new NAPTR record
// var_dump(apiCall('dns/add-record.json', 'domain-name=1a-2test.com&record-type=NAPTR&ttl=3600&host=@&order=0&pref=65535&flag=U&params='.urlencode('U2+sip').'&regexp=&replace=test.com'));

// modify A record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1101252&host=a&record=20.20.20.20&ttl=3600'));

// modify AAAA record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1101253&host=aaaaaaaaa&record=20:20:20:20:20&ttl=3600'));

// modify MX record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1101254&host=&record=mailforward2.cloudns.net&ttl=86400&priority=20'));

// modify CNAME record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1101255&host=testcname&record=www.cloudns.net&ttl=60'));

// modify TXT record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1101256&host=txt&record=spf1 ip4:1.1.1.1&ttl=60'));

// modify NS record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1101257&host=1&record=ns1.cloudns.net&ttl=60'));

// modify SRV record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1101258&host=_xmpp-test._tcp&record=cloudns.net&ttl=60&priority=10&weight=20&port=30'));

// modify web redirect - without frame, without save path and redirect type 301
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1099296&host=wr&record=http://www.cloudns.net&ttl=60&frame=0&save-path=0&redirect-type=301'));

// modify web redirect - without frame, with save path and redirect type 301
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1099296&host=wr&record=http://www.cloudns.net&ttl=3600&frame=0&save-path=1&redirect-type=301'));

// modify web redirect - with frame
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1099296&host=wr&record=http://www.cloudns.net&ttl=3600&frame=1&frame-title=t&frame-keywords=k&frame-description=d'));

// modify RP record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=1495015&host=qwe12312331232123&ttl=3600&mail=my@mail.com&txt=txt.domain.com'));

// modify SSHFP record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=domain.com&record-id=3935669&host=apitest&record=8fd1935a5739a39fe6c79f2754076880c7d79bd2&ttl=300&algorithm=1&fptype=1'));

// modify NAPTR record
// var_dump(apiCall('dns/mod-record.json', 'domain-name=1a-2test.com&record-id=15321491&ttl=3600&host=@&order=0&pref=65535&flag=U&params='.urlencode('U3+sip').'&regexp=&replace=test.com'));

// list master servers of slave zone
// var_dump(apiCall('dns/master-servers.json', 'domain-name=domain.com'));

// add master ip v4 to slave zone
// var_dump(apiCall('dns/add-master-server.json', 'domain-name=domain.com&master-ip=10.10.10.1'));

// add master ip v6 to slave zone
// var_dump(apiCall('dns/add-master-server.json', 'domain-name=domain.com&master-ip=2:2:2:2:2'));

// del master server
// var_dump(apiCall('dns/delete-master-server.json', 'domain-name=domain.com&master-id=1099228'));

// soa details
// var_dump(apiCall('dns/soa-details.json', 'domain-name=domain.com'));

// modify soa details
// var_dump(apiCall('dns/modify-soa.json', 'domain-name=domain.com&primary-ns=ns2.cloudns.net&admin-mail=test@cloudns.net&refresh=3000&retry=3000&expire=2000000&default-ttl=3000'));

// list mail forwards
// var_dump(apiCall('dns/mail-forwards.json', 'domain-name=domain.com'));

// add mail forward
// var_dump(apiCall('dns/add-mail-forward.json', 'domain-name=domain.com&box=&host=&destination=test@gmail.com));
// var_dump(apiCall('dns/add-mail-forward.json', 'domain-name=domain.com&box=support&host=test&destination=test@gmail.com'));

// delete mail forward
// var_dump(apiCall('dns/delete-mail-forward.json', 'domain-name=domain.com&mail-forward-id=3558'));

// domain update status
// var_dump(apiCall('dns/update-status.json', 'domain-name=domain.com'));

// domain is updated
// var_dump(apiCall('dns/is-updated.json', 'domain-name=domain.com'));


// hourly statistics
// var_dump(apiCall('dns/statistics-hourly.json', 'domain-name=domain.com&year=2012&month=12&day=02'));

// daily statistics
// var_dump(apiCall('dns/statistics-daily.json', 'domain-name=domain.com&year=2012&month=10'));

// monthly statistics
// var_dump(apiCall('dns/statistics-monthly.json', 'domain-name=domain.com&year=2011'));

// yearly statistics
// var_dump(apiCall('dns/statistics-yearly.json', 'domain-name=domain.com'));

// last 30 days statistics
// var_dump(apiCall('dns/statistics-last-30-days.json', 'domain-name=domain.com'));

// add cloud/bulk domain
// var_dump(apiCall('dns/add-cloud-domain.json', 'domain-name=bulk-master.com&cloud-domain-name=bulk-domain.net'));

// list cloud/bulk domains
// var_dump(apiCall('dns/list-cloud-domains.json', 'domain-name=bulk-master.com'));
 
// set master of cloud/bulk domains
// var_dump(apiCall('dns/set-master-cloud-domain.json', 'domain-name=bulk-domain.net'));

// delete cloud/bulk domain
// var_dump(apiCall('dns/delete-cloud-domain.json', 'domain-name=bulk-domain.net'));

// list of axfr per domain
// var_dump(apiCall('dns/axfr-list.json', 'domain-name=domain.com'));

// adds new axfr record
// var_dump(apiCall('dns/axfr-add.json', 'domain-name=domain.com&ip=1.2.3.5'));

// deletes axfr record
// var_dump(apiCall('dns/axfr-remove.json', 'domain-name=domain.com&id=0000'));

// get pages count
// var_dump(apiCall('dns/get-pages-count.json', 'rows-per-page=10&search=domain'));

// list all zones (pagination)
// var_dump(apiCall('dns/list-zones.json', 'page=1&rows-per-page=10&search=domain'));

// get zone count and user type limit
// var_dump(apiCall('dns/get-zones-stats.json', ''));

 // copy records from zone
// var_dump(apiCall('dns/copy-records.json', 'domain-name=domain.com&from-domain=from-domain.com&delete-current-records=1'));

// get Dynamic URL of a record
// var_dump(apiCall('dns/get-dynamic-url.json', 'domain-name=domain.com&record-id=000000'));

// import records via transfer
// var_dump(apiCall('dns/axfr-import.json', 'domain-name=domain.com&server=1.1.1.1'));
 
// change record's status
// var_dump(apiCall('dns/change-record-status.json', 'domain-name=domain.com&record-id=12358&status=1'));

// change zone's status
// var_dump(apiCall('dns/change-status.json', 'domain-name=domain.com&status=0'));

// get zone info
// var_dump(apiCall('dns/get-zone-info.json', 'domain-name=domain.com'));

// get parked templates
// var_dump(apiCall('dns/get-parked-templates.json', ''));

// get parked settings
// var_dump(apiCall('dns/get-parked-settings.json', 'domain-name=domain.com'));

// set parked settings
// var_dump(apiCall('dns/set-parked-settings.json', 'domain-name=domain.com&template=1&contact-form=1&title=Title of page&description=Description of page&keywords=key, words, of, page'));

// get mail forwards stats
// var_dump(apiCall('dns/get-mail-forwards-stats.json', ''));

// update zone
// var_dump(apiCall('dns/update-zone.json', 'domain-name=domain.com'));



/* *** reseller module *** */

// checks for domain availability
// var_dump(apiCall('domains/check-available.json', 'name=your-domain&tld[]=uk&tld[]=com&tld[]=net'));

// get domain info
// var_dump(apiCall('domains/domain-info.json', 'domain-name=your-domain.com'));

// get name server
// var_dump(apiCall('domains/get-nameservers.json', 'domain-name=your-domain.net'));
 
// set name server
// var_dump(apiCall('domains/set-nameservers.json', 'domain-name=your-domain.net&nameservers[]=pns1.cloudns.net&nameservers[]=pns3.cloudns.net'));
 
// get contact details
// var_dump(apiCall('domains/get-contacts.json', 'domain-name=your-domain.net'));
 
// set contact details
// var_dump(apiCall('domains/set-contacts.json', 'domain-name=your-domain.net&type=reg&name=John Doe&company=Company LTD&address1=25 Street str&city=Dallas&country=us&telno=123456&telnocc=001&email=user@gmail.com&zip=5000'));

// get transfer code
// var_dump(apiCall('domains/get-transfer-code.json', 'domain-name=your-domain.net'));

// set transfer lock
// var_dump(apiCall('domains/edit-transfer-lock.json', 'domain-name=your-domain.net&status=1'));

// set privacy protection
// var_dump(apiCall('domains/edit-privacy-protection.json', 'domain-name=your-domain.net&status=0'));

// get child name servers
// var_dump(apiCall('domains/get-child-nameservers.json', 'domain-name=your-domain.net'));

// set child name servers
// var_dump(apiCall('domains/add-child-nameservers.json', 'domain-name=your-domain.net&host=ns1&ip=1.1.1.1'));

// delete child name servers
// var_dump(apiCall('domains/delete-child-nameservers.json', 'domain-name=your-domain.net&host=ns1&ip=1.1.1.1'));

// modify child name servers
// var_dump(apiCall('domains/modify-child-nameservers.json', 'domain-name=your-domain.net&host=ns1&old-ip=1.1.1.1&new-ip=1.1.1.2'));

// order new domain
// var_dump(apiCall('domains/order-new-domain.json', 'domain-name=your-domain&tld=net&period=1&name=John Doe&company=Company ltd&address=25 Street str&city=Dallas&country=Texas&telno=123456&telnocc=001&mail=user@gmail.com&zip=5000'));

// order renew domain
// var_dump(apiCall('domains/order-renew-domain.json', 'domain-name=your-domain.net&period=1'));

// order transfer domain
// var_dump(apiCall('domains/order-transfer-domain.json', 'domain-name=your-domain&tld=net&name=John Doe&company=Company ltd&address=25 Street str&city=Dallas&country=Texas&telno=123456&telnocc=001&mail=user@gmail.com&zip=5000&transfer-code=fdsfsdf'));

// get raa status
// var_dump(apiCall('domains/get-raa-status.json', 'domain-name=your-domain'));

// resend raa verification
// var_dump(apiCall('domains/resend-raa-verification.json', 'domain-name=your-domain'));



 /* *** sub users *** */
 
// add sub user
// var_dump(apiCall('sub-users/add.json', 'password=PASSWORD&zones=12&mail-forwards=13&ip=1.2.3.5'));
 
// get sub user info
// var_dump(apiCall('sub-users/get-info.json', 'id=ID'));

// get sub users pages count
// var_dump(apiCall('sub-users/get-pages-count.json', 'rows-per-page=10'));

// list of sub users
// var_dump(apiCall('sub-users/list-sub-users.json', 'page=1&rows-per-page=10'));

// modify zones limit
// var_dump(apiCall('sub-users/modify-zones-limit.json', 'id=ID&zones=31'));

// modify mail forwards limit
// var_dump(apiCall('sub-users/modify-mail-forwards-limit.json', 'id=ID&mail-forwards=31'));

// add ip
// var_dump(apiCall('sub-users/add-ip.json', 'id=ID&ip=31.31.31.31'));

// remove ip
// var_dump(apiCall('sub-users/remove-ip.json', 'id=ID&ip=31.31.31.31'));

// modify status
// var_dump(apiCall('sub-users/modify-status.json', 'id=ID&status=1'));

// modify password
// var_dump(apiCall('sub-users/modify-password.json', 'id=ID&password=PASSWORD'));

// get zones list
// var_dump(apiCall('sub-users/zones.json', 'id=ID'));

// delegate zone
// var_dump(apiCall('sub-users/delegate-zone.json', 'id=ID&zone=domain.com'));

// remove zone delegation
// var_dump(apiCall('sub-users/remove-zone-delegation.json', 'id=ID&zone=domain.com'));

// delete sub user
// var_dump(apiCall('sub-users/delete.json', 'id=ID'));
