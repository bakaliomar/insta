<?php
/**
 * @framework       MailTng Framework
 * @version         1.1
 * @author          MailTng Team
 * @copyright       Copyright (c) 2015 - 2016.	
 * @license		
 * @link	
 */ 
use ma\mailtng\database\Database as Database;
use ma\mailtng\types\Arrays as Arrays;
use ma\mailtng\configuration\Configuration as Configuration;
use ma\applications\mailtng\models\admin\Ip as Ip;
use ma\mailtng\files\Paths as Paths;
use ma\mailtng\ssh2\SSH as SSH;
use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
use ma\applications\mailtng\models\admin\Domain as Domain;
use ma\mailtng\types\Strings as Strings;
use ma\mailtng\os\System as System;
use ma\mailtng\www\Domains as Domains;
use ma\applications\mailtng\models\admin\Server as Server;
use ma\mailtng\encryption\Crypto as Crypto;
use ma\mailtng\api\NameCheap as NameCheap;
/**
 * @name            install.php 
 * @description     a native script that installs a production server
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */

# to ensure scripts are not called from outside of the framework 
define('MAILTNG_FMW',true);  

# get the application name
$appPrefix = trim(basename(dirname(dirname(__DIR__))));

# require the main configuration of the framework 
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/configs/init.conf.php';

# require request init configurations ( application init and database , cache ... )
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/configs/request.init.conf.php';

# require the helper
require_once Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'servers' . DS . 'helper.php';

# check if the parameters has been sent 
if(count($argv) == 2)
{
    # connect to the default database 
    Database::secureConnect();
    
    # extract all the parameters 
    $parameters = Crypto::AESDecrypt($argv[1]);
    
    # get the main values
    $user = $parameters['user'];
    $serverId = $parameters['server-id'];
    $version = intval($parameters['server-version']);
    $useSubDomains = Arrays::getElement($parameters,'use-subdomains');

    # empty the log file 
    System::executeCommand("> " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.log');
    System::executeCommand("echo 'in progress' > " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.process');
    
    # get installation configuration
    $updateIps = Arrays::getElement($parameters,'update-ips');
    $updateRecords = Arrays::getElement($parameters,'update-records');
    $installServices = Arrays::getElement($parameters,'install-services');
    $installMailScripts = Arrays::getElement($parameters,'install-mail-scripts');
    $installTracking = Arrays::getElement($parameters,'install-tracking');
    $installWebmail = Arrays::getElement($parameters,'install-webmail');
    $installPMTA = Arrays::getElement($parameters,'install-pmta');
    
    # get ip/domain mapping
    $ipsDomainsMapping = explode(';',trim(base64_decode($parameters['domain-mapping']),';'));
    $prefixes = array('shop','blog','ads','club','sales','portal','store','offers','news','app','offers','report','games','email','webmail','jobs','app','interact','goodies','leads');
    
    for ($index1 = 0; $index1 < 255; $index1++)
    {
        $prefixes[] = Strings::generateRandomText(rand(3,5),true,false,true,false);
    }
    
    $nameCheap = new NameCheap();
    
    # retrieve the servers and ips
    $server = Server::first(true,array('id = ?',$serverId));
    $ips = Ip::all(true,array('server_id = ?',$serverId));

    # print progress message
    showProgressMessage('Installing server : ' . $server['name'] . ' ...');
    
    # print progress message
    showProgressMessage('Connecting to server via SSH ...');
        
    $ssh = new SSH($server['main_ip'],new SSHPasswordAuthentication($server['username'],$server['password']),$server['ssh_port']);

    if($ssh->isConnected())
    {
        $prefix = $server['username'] != 'root' ? "echo {$server['password']} | sudo -S " : '';
        
        # print progress message
        showProgressMessage('Server connected !');
 
        # initializing assets directory
        $assetsDirectory = Paths::getCurrentApplicationRealPath() . DS . DEFAULT_ASSETS_DIRECTORY;
        
        # check if installing / reinstalling service is enabled 
        if($installServices == 'enabled')
        {
            # print progress message
            showProgressMessage('Updating services ( apatche , ssh , firewall ) ...');

            removeServices($version,$ssh,$prefix);
            stopFireWall($version,$ssh,$prefix);
            installServices($ssh,$prefix);
            installPHP($ssh,$version,$assetsDirectory,$prefix);
            restartApache($version,$ssh,$prefix);

            # print progress message
            showProgressMessage('Updating services ( apatche , ssh , firewall ) completed !');
        }
        
        # check if updating ips is enabled 
        if($updateIps == 'enabled')
        {
            # print progress message
            showProgressMessage('Updating IPs ...');
                        showProgressMessage($ipsDomainsMapping[0].'-'.$ipsDomainsMapping[1].'dfdfdf');
            if(count($ipsDomainsMapping))
            {
                $ipsDomainsMaps = array();

                if($useSubDomains == 'enabled')
                {
                    $map = explode('=',$ipsDomainsMapping[0]);
                    
                    if(count($map) == 2)
                    {
                        $ip = Ip::first(true,array('value = ?',$map[0]));
                        $domain = Domain::first(true,array('id = ?',intval($map[1])));
                        
                        if(count($domain))
                        {
                            $ipId = (count($ip) > 0) ? $ip['id'] : 0;
						    $ipvmta =str_replace(':','-',$map[0] );

                            $ipsDomainsMaps[] = array('ip' => array('id' => $ipId , 'value' => $ipvmta),'domain' => array('id' => $map[1] , 'value' => $domain['value'])); 
                            $subs = count($ipsDomainsMapping) > 2 ? array_rand($prefixes,(count($ipsDomainsMapping) - 1)) : array(array_rand($prefixes));
                            
                            for ($index = 1; $index < count($ipsDomainsMapping); $index++) 
                            {
                                $map = explode('=',$ipsDomainsMapping[$index]);
                                $ip = Ip::first(true,array('value = ?',$map[0]));
                                $ipId = (count($ip) > 0) ? $ip['id'] : 0;
								
								$ipvmta =str_replace(':','-',$map[0] );
                                $ipsDomainsMaps[] = array('ip' => array('id' => $ipId , 'value' => $ipvmta),'domain' => array('id' => 0 , 'value' => $prefixes[$subs[($index-1)]] . '.' . $domain['value'])); 
                            } 
                        }  
                    }      
                }
                else
                {
                    foreach ($ipsDomainsMapping as $map) 
                    {
                        $map = explode('=',$map);

                        if(count($map) == 2)
                        {
                            $ip = Ip::first(true,array('value = ?',$map[0]));
                            $domain = Domain::first(true,array('id = ?',intval($map[1])));

                            if(count($domain))
                            {
                                $ipId = (count($ip) > 0) ? $ip['id'] : 0;
                                $ipsDomainsMaps[] = array('ip' => array('id' => $ipId , 'value' => $map[0]),'domain' => array('id' => $map[1] , 'value' => $domain['value'])); 
                            }  
                        }
                    }
                }
            }

            # check if the IP/Domain Mapping is correct 
            if(count($ipsDomainsMaps) == 0)
            {
                triggerFatalError("IPs/Domains mapping not found !",$serverId,$ssh);
            }
            
            foreach ($ipsDomainsMaps as $map) 
            {
                if(key_exists('ip',$map) && count($map['ip']) == 2 && key_exists('domain',$map) && count($map['domain']) == 2)
                {
                    $ip = $map['ip'];
                    $domain = $map['domain'];
                    
                    # update the old domain availability ( if any )
                    Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.domains SET ip_id = 0 , domain_status = 'Available' WHERE id = (SELECT id FROM admin.domains WHERE ip_id = ( SELECT id FROM admin.ips WHERE value = '" . $ip['value'] . "' LIMIT 1 ) LIMIT 1)");
                    
                    if($ip['id'] > 0)
                    {
                        $ipObject = new Ip(array('id' => $ip['id'])); 
                    }
                    else
                    {
                        $ipObject = new Ip();
                    }
                    
                    $ipObject->setStatus_id(1);
                    $ipObject->setServer_id(intval($serverId));
                    $ipObject->setValue($ip['value']);
                    $ipObject->setRdns(trim($domain['value']));
                    $ipObject->setCreated_by(intval(Arrays::getElement($user,'id')));
                    $ipObject->setCreated_at(date("Y-m-d"));
                    $ipObject->setLast_updated_by(intval(Arrays::getElement($user,'id')));
                    $ipObject->setLast_updated_at(date("Y-m-d")); 
                    $result = $ipObject->save(true);
                    $ipId = ($ip['id'] > 0) ? $ip['id'] : $result;
                    
                    # update the old domain availability ( if any ) 
                    Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.domains SET ip_id = '" . $ipId . "' , domain_status = 'Taken' WHERE value = '" . trim($domain['value']) . "'");
                }
            }			   
           $ips = Ip::all(true,array('server_id = ? AND value like ?',array($serverId,'%.%.%.%')));
		  
            if(count($ips))
            {
                $mainIp = null;
                $found = false;
                
                foreach ($ips as $ip)
                {
                    if($ip['rdns'] != '')
                    {
                        $mainIp = $ip;
                        $found = true;
                    }
                    
                    if($found == true)
                    {
                        break;
                    }
                }
                
                $mainIp = $mainIp == null ? $ips[0] : $mainIp ;
                
                installDKIM($ssh,$assetsDirectory,$version,$prefix);
                restartOpenDKIM($ssh,$version,$prefix);
                
                $hostname = trim($mainIp['rdns']);
                $hostsFile = trim($mainIp['value']). " mail." . trim($mainIp['rdns']) . " mail" . PHP_EOL;
                $resolvFile = "nameserver 8.8.8.8" . PHP_EOL . "nameserver 8.8.4.4";
                
                # upload hosts files
                $ssh->scp('send',array('/etc/resolv.conf'),$resolvFile);
                $ssh->scp('send',array('/etc/hosts'),$hostsFile);
    
                if(!empty($hostname))
                {
                    $networkFile = explode(PHP_EOL,  trim($ssh->cmd("{$prefix}cat /etc/sysconfig/network",true),PHP_EOL));

                    if(count($networkFile))
                    {
                        foreach ($networkFile as &$value) 
                        {
                            if(Strings::startsWith(trim($value),"HOSTNAME"))
                            {
                                $value = 'HOSTNAME="mail.' . $hostname . '"';
                            }
                        }
                    }

                    $ssh->scp('send',array('/etc/sysconfig/network'),implode(PHP_EOL, $networkFile));
                    $ssh->cmd("{$prefix}hostname mail.$hostname");

                    # update server's hostname
                    Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.servers SET host_name = 'mail.$hostname' WHERE id = " . trim(Arrays::getElement($server,'id')));
                }

                # check if updating records is enabled 
                if($updateRecords == 'enabled')
                {
                    # print progress message
                    showProgressMessage('Updating Records ( DNS Records , SPF Records , DKIM Records ) ...');
            
                    if($useSubDomains == 'enabled')
                    {
                        $index = 10;

                        $dkim = generateDKIM($ssh,$mainIp['rdns'],$prefix);
                        $spf = "v=spf1 ";
                        
                        foreach ($ips as $ip) 
                        {
                            if(count($ip))
                            {
						if(filter_var($ip['value'],FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){ 
                                $spf .= "ip4:{$ip['value']} ";
						}
                            }
                        }
                        
                        $spf .= "-all";
                        
                        $dnsRecords = array(

                            "EmailType" => "MX",

                            "HostName1" => "@",
                            "RecordType1" => "A",
                            "Address1" => $mainIp['value'],
                            "TTL1" => "60",

                            "HostName2" => "www",
                            "RecordType2" => "A",
                            "Address2" => $mainIp['value'],
                            "TTL2" => "60",

                            "HostName3" => "mail",
                            "RecordType3" => "A",
                            "Address3" => $mainIp['value'],
                            "TTL3" => "60",

                            "HostName4" => "ftp",
                            "RecordType4" => "A",
                            "Address4" => $mainIp['value'],
                            "TTL4" => "60",

                            "HostName5" => "ns1",
                            "RecordType5" => "A",
                            "Address5" => $mainIp['value'],
                            "TTL5" => "60",

                            "HostName6" => $mainIp['rdns'],
                            "RecordType6" => "NS",
                            "Address6" => "ns1.{$mainIp['rdns']}",
                            "TTL6" => "60",

                            "HostName7" => "@",
                            "RecordType7" => "MX",
                            "Address7" => "mail.$hostname",
                            "MXPre7" => "10",
                            "TTL7" => "60",

                            "HostName8" => $dkim[0],
                            "RecordType8" => "TXT",
                            "Address8" => $dkim[1],
                            "TTL8" => "60",

                            "HostName9" => "@",
                            "RecordType9" => "TXT",
                            "Address9" => $spf,
                            "TTL9" => "60"
                        );
                      $ipss = Ip::all(true,array('server_id = ?',$serverId));
                        foreach ($ipss as $ip) 
                        {
							////mohamed
							 $ip_v=str_replace('-',':',$ip['value']);
							if(filter_var($ip_v,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){ 
							
                            if(count($ip) && $ip['id'] != $mainIp['id'])
                            {
                                $sub = Arrays::getElement(explode('.',$ip['rdns']),0);

                                # A record
                                $dnsRecords["HostName$index"] = $sub;
                                $dnsRecords["RecordType$index"] = "A";
                                $dnsRecords["Address$index"] = $ip_v;
                                $dnsRecords["TTL$index"] = "60";
                                $index++;

                                # SPF record
                                $dnsRecords["HostName$index"] = $sub;
                                $dnsRecords["RecordType$index"] = "TXT";
                                $dnsRecords["Address$index"] = "v=spf1 ip4:{$ip_v} -all";
                                $dnsRecords["TTL$index"] = "60";
                                $index++;
                            }
							
							}
							
                       	if(filter_var($ip_v,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)){ 
							
                            if(count($ip) && $ip['id'] != $mainIp['id'])
                            {
                                $sub = Arrays::getElement(explode('.',$ip['rdns']),0);

                                # A record
                                $dnsRecords["HostName$index"] = $sub;
                                $dnsRecords["RecordType$index"] = "AAAA";
                                $dnsRecords["Address$index"] = $ip_v;
                                $dnsRecords["TTL$index"] = "60";
                                $index++;

                                # SPF record
                                $dnsRecords["HostName$index"] = $sub;
                                $dnsRecords["RecordType$index"] = "TXT";
                                $dnsRecords["Address$index"] = "v=spf1 ip6:{$ip_v} -all";
                                $dnsRecords["TTL$index"] = "60";
                                $index++;
                            }
							
							}
                       

					   }
                    //  error_log(print_r($dnsRecords, TRUE));
                        # update records
                        $nameCheap->setDomainRecords($mainIp['rdns'],$dnsRecords);  
                    }
                    else
                    {
                        foreach ($ips as $ip) 
                        {
                            if(count($ip))
                            {
                                $dkim = generateDKIM($ssh,$ip['rdns'],$prefix);

                                $dnsRecords = array(

                                    "EmailType" => "MX",

                                    "HostName1" => "@",
                                    "RecordType1" => "A",
                                    "Address1" => $ip['value'],
                                    "TTL1" => "60",

                                    "HostName2" => "www",
                                    "RecordType2" => "A",
                                    "Address2" => $ip['value'],
                                    "TTL2" => "60",

                                    "HostName3" => "mail",
                                    "RecordType3" => "A",
                                    "Address3" => $ip['value'],
                                    "TTL3" => "60",

                                    "HostName4" => "ftp",
                                    "RecordType4" => "A",
                                    "Address4" => $ip['value'],
                                    "TTL4" => "60",

                                    "HostName5" => "ns1",
                                    "RecordType5" => "A",
                                    "Address5" => $ip['value'],
                                    "TTL5" => "60",

                                    "HostName6" => $ip['rdns'],
                                    "RecordType6" => "NS",
                                    "Address6" => "ns1.{$ip['rdns']}",
                                    "TTL6" => "60",

                                    "HostName7" => "@",
                                    "RecordType7" => "MX",
                                    "Address7" => "mail.$hostname",
                                    "MXPre7" => "10",
                                    "TTL7" => "60",

                                    "HostName8" => $dkim[0],
                                    "RecordType8" => "TXT",
                                    "Address8" => $dkim[1],
                                    "TTL8" => "60",

                                    "HostName9" => "@",
                                    "RecordType9" => "TXT",
                                    "Address9" => "v=spf1 ip4:{$ip['value']} -all",
                                    "TTL9" => "60",

                                    "HostName10" => "_dmarc",
                                    "RecordType10" => "TXT",
                                    "Address10" => "v=DMARC1; p=none; sp=none; rf=afrf; pct=100; ri=86400",
                                    "TTL10" => "60"
                                );
                                
                                # update records
                                $nameCheap->setDomainRecords($ip['rdns'],$dnsRecords);
                            }
                        }
                    }
                    
                    # print progress message
                    showProgressMessage('Updating Records ( DNS Records , SPF Records , DKIM Records ) completed !');
                }
            }
            
            # print progress message
            showProgressMessage('Updating IPs completed !');
        }

        # check if installing / reinstalling mail scripts is enabled 
        if($installMailScripts == 'enabled')
        {
            # print progress message
            showProgressMessage('Installing MailTng Mailing scripts ...');
	
            
            $path = $assetsDirectory . DS . 'server' . DS . 'scripts' . DS;
            $ssh->cmd("{$prefix}rm -rf /usr/mailtng");
            
            # wait for 2 seconds 
            sleep(2);
			
            
            $ssh->cmd("{$prefix}mkdir -p /usr/mailtng");
            $ssh->cmd("{$prefix}mkdir -p /usr/mailtng/scripts/calculations");
            $ssh->cmd("{$prefix}mkdir -p /usr/mailtng/scripts/drops");
            $ssh->cmd("{$prefix}mkdir -p /usr/mailtng/scripts/helpers");
            $ssh->cmd("{$prefix}mkdir -p /usr/mailtng/tmp/drops");
            $ssh->cmd("{$prefix}mkdir -p /usr/mailtng/tmp/logs");
            $ssh->cmd("{$prefix}mkdir -p /usr/mailtng/tmp/proccess");
            $ssh->scp('send',array('/usr/mailtng/scripts/calculations' . DS . 'read.php'),str_replace('$url = "";','$url = "' . $parameters['api-link'] . '";',file_get_contents($path . 'calculations' . DS . 'read.php')));
            $ssh->scp('send',array($path . 'calculations' . DS . 'move.php','/usr/mailtng/scripts/calculations' . DS . 'move.php'));
            $ssh->scp('send',array($path . 'drops' . DS . 'worker.php','/usr/mailtng/scripts/drops' . DS . 'worker.php'));
            $ssh->scp('send',array($path . 'drops' . DS . 'main.php','/usr/mailtng/scripts/drops' . DS . 'main.php'));
            $ssh->scp('send',array('/usr/mailtng/scripts/helpers' . DS . 'main.php'),str_replace('$url = "";','$url = "' . $parameters['api-link'] . '";',file_get_contents($path . 'helpers' . DS . 'main.php')));
            
            # wait for 2 seconds 
            sleep(2);
             
            # add previliges to apache
            $ssh->cmd("{$prefix}chown -R apache:apache /usr/mailtng");
            
            $ssh->cmd("{$prefix}echo \"*/5 * * * * php /usr/mailtng/scripts/calculations/move.php\" > /tmp/cron");
            $ssh->cmd("{$prefix}echo \"*/10 * * * * php /usr/mailtng/scripts/calculations/read.php\" >> /tmp/cron");
			$ssh->cmd("{$prefix}echo \"*/5 * * * * perl /root/firewall.pl\" >> /tmp/cron");
            $ssh->cmd("{$prefix}crontab /tmp/cron");
            $ssh->cmd("{$prefix}rm -rf /tmp/cron");
            
            restartCrond($version, $ssh);
            
            # print progress message
            showProgressMessage('Installing MailTng Mailing scripts completed !');
        }

        # check if installing / reinstalling tracking application is enabled 
        if($installTracking == 'enabled')
        {
            # print progress message
            showProgressMessage('Installing MailTng Tracking application ...');

            # get server domains
            $ips = Ip::all(true,array('server_id = ?',$serverId));
            
            # uploading the zipped app 
            $ssh->cmd("{$prefix}rm -rf /var/mailtng/");
            $ssh->cmd("{$prefix}mkdir -p /var/mailtng/");
            $ssh->scp('send',array($assetsDirectory . DS . 'server' . DS . 'tracking.zip','/home/tracking.zip'));
			$ssh->scp('send',array($assetsDirectory . DS . 'server' . DS . 'firewall.pl','/root/firewall.pl'));

            $ssh->cmd("{$prefix}unzip /home/tracking.zip -d /var/mailtng/",true);
            $ssh->cmd("{$prefix}rm -rf /home/tracking.zip");
            $ssh->cmd("{$prefix}chown -R apache:apache /var/mailtng/tmp/");

            $all = $version == 7 ? 'Require all granted' : 'Allow from all';
            $vhostsTemplate = file_get_contents($assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'vhosts.tpl');
            $vhostsContent = "# Redirect Vhosts " . PHP_EOL . str_replace('$P{ALL}',$all,$vhostsTemplate);
            $trackingMapping = "; Redirect Mapping Configuration File" . PHP_EOL;
            $trackingSitesArray = array();

            foreach ($ips as $ip) 
            {
                if(count($ip) > 0 && count(explode('.',$ip['rdns'])) == 2)
                {
                    $random = Strings::generateRandomText(5,true, false, true, false);
                    $trackingMapping .= str_replace('.','_',trim($ip['rdns'])) . '=' . $random . PHP_EOL;
                    $siteName = ucfirst(Arrays::getElement(explode('.',trim($ip['rdns'])),0));
                    $siteContact = "contact@" . trim($ip['rdns']);
                    $sitePhone = "+212 1 11 11 11 11";

                    # upload landing site
                    $ssh->cmd("{$prefix}mkdir -p /var/mailtng/web/$random/");
                    $ssh->scp('send',array($assetsDirectory . DS . 'server' . DS . 'site.zip','/home/site.zip'));
                    $ssh->cmd("{$prefix}unzip /home/site.zip -d /var/mailtng/web/$random/",true);
                    $ssh->cmd("{$prefix}rm -rf /home/site.zip");
                    $ssh->cmd("{$prefix}sed -i 's/" . '\$P{WEBSITE_NAME}' . "/$siteName/g' /var/mailtng/web/$random/index.html");
                    $ssh->cmd("{$prefix}sed -i 's/" . '\$P{WEBSITE_EMAIL}' . "/$siteContact/g' /var/mailtng/web/$random/index.html");
                    $ssh->cmd("{$prefix}sed -i 's/" . '\$P{WEBSITE_PHONE}' . "/$sitePhone/g' /var/mailtng/web/$random/index.html");
                }
            }

            # set landing sites config
            $ssh->scp('send',array('/var/mailtng/applications/tracking/configs/domains.ini'),$trackingMapping);

            $ssh->cmd("{$prefix}rm -rf /etc/httpd/conf.d/tracking.conf");
            $ssh->scp('send',array('/etc/httpd/conf.d/tracking.conf'),$vhostsContent);

            $link = str_replace(RDS,'\/',$parameters['api-link']);
            $ssh->cmd("{$prefix}sed -i 's/\$url = \"\";/\$url=\"$link\";/g' /var/mailtng/applications/tracking/scripts/actions.php;");
            
            # restart apache
            restartApache($version, $ssh,$prefix);

            # print progress message
            showProgressMessage('Installing MailTng Tracking application completed !');
        }

        # check if installing / reinstalling powerMTA is enabled 
        if($installPMTA == 'enabled')
        {
            # print progress message
            showProgressMessage('Installing PowerMTA 4 ...');
            
            # get pmta port
            $configuration = new Configuration(array( "type" => "ini" ));
            $result = $configuration->initialize()->parse(Paths::getCurrentApplicationRealPath() . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'pmta',false);
            $pmtaPort = (count($result) > 0 && key_exists('pmta_http_port',$result)) ? $result['pmta_http_port'] : 8080;
                    
            # get the list of ips in that server 
            $ips = Ip::all(true,array('server_id = ?',$serverId));

            # check if the IP/Domain Mapping is correct 
            if(count($ips) == 0)
            {
                triggerFatalError("This Server contains not IPs in the database !",$serverId,$ssh);
            }
            
            uninstallPMTA($version, $ssh,$prefix); 
            
            $ssh->scp('send',array($assetsDirectory . DS . 'pmta' . DS . 'pmta.rpm','/home/pmta.rpm'));
            $ssh->cmd("{$prefix}rpm -Uvh /home/pmta.rpm;",true);
            $ssh->cmd("{$prefix}rm -rf /etc/pmta/license-notice;");
            $ssh->cmd("{$prefix}rm -rf /etc/pmta/config;");
            $ssh->cmd("{$prefix}rm -rf /etc/pmta/config-defaults;");
            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/keys/;");
            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/conf/;");

            $ssh->scp('send',array($assetsDirectory . DS . 'pmta' . DS . 'license','/etc/pmta/license'));
            $ssh->scp('send',array($assetsDirectory . DS . 'pmta' . DS . 'vmta_all.conf','/etc/pmta/conf/'));
            $ssh->scp('send',array($assetsDirectory . DS . 'pmta' . DS . 'vmta_isp.conf','/etc/pmta/conf/'));
			
            $config = file_get_contents($assetsDirectory . DS . 'pmta' . DS . 'config');
            $vmtaconfig = file_get_contents($assetsDirectory . DS . 'pmta' . DS . 'vmta_all.conf');

            # manage VMTAs 
            $vmtas = '';
            $hostname = '';
            $found = false;

            foreach ($ips as $ip)
            {
                if($ip['rdns'] != '')
                {
                    $hostname = $ip['rdns'];
                    $found = true;
                }

                if($found == true)
                {
                    break;
                }
            }
           
            foreach ($ips as $row) 
            {
               if($row['rdns'] != '')
               {
                   $domain = count(explode('.',$row['rdns'])) > 2 ? Domains::getDomainFromURL($row['rdns']) : $row['rdns'];
				   $ipvmta =str_replace('-',':',$row['value']);
                    $vmta = str_replace(array('$P{IP}','$P{DOMAIN}','$P{VMTA}'),array($ipvmta,$domain,$row['value']),file_get_contents($assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'vmta.tpl')) . PHP_EOL;
                    $check = preg_replace( "/\r|\n/","", trim($ssh->cmd($prefix . '[ -f /etc/opendkim/keys/'.$domain.'/mail.private ] && echo "Found" || echo "Not found"',true)));

                    if($check == 'Found')
                    {
                        $dkim = "domain-key mail,$domain,/etc/pmta/keys/$domain/mail.private";
                        $vmta = str_replace('$P{DKIM}',$dkim, $vmta);

                        if(count(explode('.',$domain)) == 2)
                        {
                            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/keys/$domain/;");
                            $ssh->cmd("{$prefix}cp /etc/opendkim/keys/$domain/mail.private /etc/pmta/keys/$domain/mail.private;");
                        }
                    }
                    else
                    {
                        $vmta = str_replace('$P{DKIM}','', $vmta);
                    }

                    $vmtas .= $vmta;
               } 
            }

            $vmtaconfig = str_replace('$_VMTAS',$vmtas,$vmtaconfig);
            
            # manage hostname
            $config = str_replace('$_HOST','host-name mail.' . $hostname,$config);
           
            # set pmta http port
            $config = str_replace('$_PORT',$pmtaPort,$config);

            # upload the config file 
            $ssh->scp('send',array($assetsDirectory . DS . 'pmta' . DS . 'vmta_isp.conf','/etc/pmta/conf/vmta_isp.conf'));
            $ssh->scp('send',array('/etc/pmta/config'),$config);
            $ssh->scp('send',array('/etc/pmta/conf/vmta_all.conf'),$vmtaconfig);
            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/delivered/;");
            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/delivered/archived/;");
            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/delivered/moved/;");
            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/bounces;");
            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/bounces/archived/;");
            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/bounces/moved/;");
            $ssh->cmd("{$prefix}mkdir -p /etc/pmta/deffered/;");
            $ssh->cmd("{$prefix}mkdir -p /var/spool/mailtng/pickup/;");
            $ssh->cmd("{$prefix}mkdir -p /var/spool/mailtng/bad/;");
            $ssh->cmd("{$prefix}mkdir -p /var/spool/pmta/;");

            $ssh->cmd("{$prefix}chown -R pmta:pmta /var/log/pmta/;");
            $ssh->cmd("{$prefix}chown -R pmta:pmta /var/spool/mailtng/;");
            $ssh->cmd("{$prefix}chmod 640 /etc/pmta/config;");
            $ssh->cmd("{$prefix}chmod 755 /var/spool/mailtng;");
            $ssh->cmd("{$prefix}chmod 755 /var/spool/mailtng/pickup;");
            $ssh->cmd("{$prefix}chmod 755 /var/spool/mailtng/bad;");
            $ssh->cmd("{$prefix}chown -R pmta:pmta /etc/pmta/;");
			$ssh->cmd("{$prefix}chown -R apache:apache /var/mailtng/*;");


            # print progress message
            showProgressMessage('starting PMTA ...');
            restartPMTA($version,$ssh,$prefix);

            # print progress message
            showProgressMessage('deleting Installation Temp Files ...');
            $ssh->cmd("{$prefix}rm -rf /home/pmta.rpm;");

            # print progress message
            showProgressMessage('Installing PowerMTA 4 completed ! click <a href="http://' . $server['main_ip'] . ':' . $pmtaPort . '" target="_blank">here</a> to see PMTA monitor !');
        }
        
        # disconnect from the server 
        $ssh->disconnect();
    }

    # print progress message
    showProgressMessage('Closing connection ...');

    # print progress message
    showProgressMessage('Server installation for ' . $server['name'] . ' completed !');

    # set proccess to 1 means completed
    System::executeCommand("echo 'completed' > " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.process');

    # disconnect from all databases 
    Database::secureDisconnect();
}
else 
{
    # print progress message
    showProgressMessage('Please check the parameters that has been sent to this script !');
    
    # set proccess to 1 means completed
    System::executeCommand("echo 'completed' > " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.process');
}