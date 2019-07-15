<?php
/**
 * @framework       MailTng Framework
 * @version         1.1
 * @author          MailTng Team
 * @copyright       Copyright (c) 2015 - 2016.	
 * @license		
 * @link	
 */ 
use ma\mailtng\ssh2\SSH as SSH;
use ma\mailtng\database\Database as Database;
use ma\mailtng\os\System as System;
/**
 * @name            helper.php 
 * @description     a native script that contains helepr methods 
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */

/**
 * @name removeServices
 * @description remove services
 * @param integer $version
 * @param SSH $ssh
 * @return 
 */
function removeServices($version,$ssh,$prefix)
{
    if (is_numeric($version) && in_array($version,array(6,7)))
    {
        $ssh->cmd("{$prefix}yum remove -y httpd;",true) . PHP_EOL;
        $ssh->cmd("{$prefix}yum remove -y libopendkim*;",true) . PHP_EOL;
        $ssh->cmd("{$prefix}yum remove -y opendkim;",true) . PHP_EOL;
        $ssh->cmd("{$prefix}yum remove -y postfix;",true) . PHP_EOL;
        $ssh->cmd("{$prefix}rm -rf /etc/httpd;",true);
        $ssh->cmd("{$prefix}rm -rf /etc/opendkim*;",true);

        if($version == 6)
        {
            $ssh->cmd("{$prefix}service sendmail stop;",true) . PHP_EOL; 
        }
        else
        {
            $ssh->cmd("{$prefix}systemctl stop sendmail;",true) . PHP_EOL;
        }
        
        $ssh->cmd("{$prefix}setenforce 0;");
        $ssh->cmd("{$prefix}setenforce Disabled;");
    }
}

/**
 * @name stopFireWall
 * @description stop firewall
 * @param integer $version
 * @param SSH $ssh
 * @return 
 */
function stopFireWall($version,$ssh,$prefix)
{
    if (is_numeric($version) && in_array($version,array(6,7)))
    {
        if($version == 6)
        {
            $ssh->cmd("{$prefix}service iptables stop;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}chkconfig iptables off;",true) . PHP_EOL;
        }
        else
        {
            $ssh->cmd("{$prefix}systemctl stop firewalld;");
            $ssh->cmd("{$prefix}systemctl disable firewalld;");
        }
    }
}

/**
 * @name installServices
 * @description install services
 * @param SSH $ssh
 * @return 
 */
function installServices($ssh,$prefix)
{
    $ssh->cmd("{$prefix}yum install -y openssh-clients;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y glibc.i686;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y pam.i686 pam;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y nano;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y rsync;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y wget;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y xinetd;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y gcc;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y make;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y httpd;",true) . PHP_EOL;      
    $ssh->cmd("{$prefix}yum install -y perl;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y mod_ssl;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y zip;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y unzip;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum update -y;",true) . PHP_EOL;
}

/**
 * @name installDKIM
 * @description install DKIM services
 * @param SSH $ssh
 * @param string $assetsDirectory 
 * @return 
 */
function installDKIM($ssh,$assetsDirectory,$version,$prefix)
{
    if($version == 7)
    {
        $ssh->cmd("{$prefix}rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm",true) . PHP_EOL;
    }
    
    $ssh->cmd("{$prefix}yum install -y libopendkim*;",true) . PHP_EOL;
    $ssh->cmd("{$prefix}yum install -y opendkim;",true) . PHP_EOL;

    # print progress message
    showProgressMessage('configuring openDKIM  ...'); 
    
    $ssh->cmd("{$prefix}opendkim-default-keygen;");
    $ssh->scp('send',array($assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'dkim-config.tpl','/etc/opendkim.conf'));
}

/**
 * @name generateDKIM
 * @description generate DKIM
 * @param SSH $ssh
 * @param string $domain 
 * @return $dkim
 */
function generateDKIM($ssh,$domain,$prefix)
{
    $dkim = array();
    
    if($domain != '')
    {
        $ssh->cmd("{$prefix}mkdir /etc/opendkim/keys/$domain;");
        $ssh->cmd("{$prefix}/usr/sbin/opendkim-genkey -D /etc/opendkim/keys/$domain/ -d $domain -s mail;");
        $ssh->cmd("{$prefix}chown -R opendkim:opendkim /etc/opendkim/keys/$domain;");
        $ssh->cmd("{$prefix}chmod 640 /etc/opendkim/keys/$domain/mail.private;");
        $ssh->cmd("{$prefix}chmod 644 /etc/opendkim/keys/$domain/mail.txt;");
        $ssh->cmd("{$prefix}'mail._dkim.$domain $domain:mail:/etc/opendkim/keys/$domain/mail.private' >> /etc/opendkim/KeyTable");
        $ssh->cmd("{$prefix}'*.$domain' >> /etc/opendkim/TrustedHosts");
        $ssh->cmd("{$prefix}'*@$domain mail._domainkey.$domain' >> /etc/opendkim/SigningTable");
        
        $content = $ssh->cmd("{$prefix}cat /etc/opendkim/keys/$domain/mail.txt",true);
        
        if(strlen($content) > 0)
        {
            $matches = array();
            
            if (preg_match('#\((([^()]+|(?R))*)\)#',$content,$matches)) 
            {
                $dkim[0] = 'mail._domainkey';
                $dkim[1] = str_replace(array("\n",'"'),array(" ",""),$matches[1]);
            }
        }
    }
    
    return $dkim;
}

/**
 * @name restartOpenDKIM
 * @description restart OpenDKIM
 * @param SSH $ssh
 * @param int $version 
 * @return
 */
function restartOpenDKIM($ssh,$version,$prefix)
{
    if($version == 6)
    {
        $ssh->cmd("{$prefix}service opendkim restart;",true) . PHP_EOL;
        $ssh->cmd("{$prefix}chkconfig opendkim on;",true) . PHP_EOL;
    }
    else
    {
        $ssh->cmd("{$prefix}systemctl stop opendkim; {$prefix}systemctl start opendkim;");
        $ssh->cmd("{$prefix}systemctl enable opendkim;");
    }
}

/**
 * @name installPHP
 * @description install PHP
 * @param SSH $ssh
 * @return 
 */
function installPHP($ssh,$version,$assetsDirectory,$prefix)
{
    if (is_numeric($version) && in_array($version,array(6,7)))
    {
        if($version == 6)
        {
            $ssh->cmd("{$prefix}wget -O /home/epel-release-6-8.noarch.rpm http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}wget -O /home/remi-release-6.rpm http://rpms.famillecollet.com/enterprise/remi-release-6.rpm",true) . PHP_EOL;  
            $ssh->cmd("{$prefix}rpm -Uvh /home/epel-release-6*.rpm",true) . PHP_EOL;
            $ssh->cmd("{$prefix}rpm -Uvh /home/remi-release-6*.rpm",true) . PHP_EOL;
            $ssh->scp('send',array($assetsDirectory . DS . 'server' . DS . 'remi.repo','/etc/yum.repos.d/remi.repo'));
            $ssh->cmd("{$prefix}yum install -y php;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-pgsql;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-mysql;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-common;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-pdo;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-opcache;",true) . PHP_EOL; 
            $ssh->cmd("{$prefix}yum install -y php-mcrypt;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-imap;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-xmlrpc;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y cronie;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum --enablerepo=remi install -y php-pecl-ssh2;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum --disablerepo=epel -y update  ca-certificates;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}rm -rf /home/epel-release-6-8.noarch.rpm /home/remi-release-6.rpm;");
        }
        else
        {
            $ssh->cmd("{$prefix}yum install -y php;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-pgsql;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-mysql;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-common;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-pdo;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-opcache;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-mcrypt;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-imap;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-xmlrpc;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y cronie;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum install -y php-pecl-ssh2;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}yum update -y ca-certificates;",true) . PHP_EOL;
        }
        
        # configure max upload and memory
        $ssh->cmd("{$prefix}sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 1G/g' /etc/php.ini;");
        $ssh->cmd("{$prefix}sed -i 's/max_file_uploads = 20/max_file_uploads = 200/g' /etc/php.ini;");
        $ssh->cmd("{$prefix}sed -i 's/post_max_size = 8M/post_max_size = 1G/g' /etc/php.ini;");
        $ssh->cmd("{$prefix}sed -i 's/memory_limit = 128M/memory_limit = 4G/g' /etc/php.ini;");
    }
}

/**
 * @name restartApache
 * @description restart apache
 * @param SSH $ssh
 * @return 
 */
function restartApache($version,$ssh,$prefix)
{
    if (is_numeric($version) && in_array($version,array(6,7)))
    {
        if($version == 6)
        {
            $ssh->cmd("{$prefix}service httpd restart;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}chkconfig httpd on;");
        }
        else
        {
            $ssh->cmd("{$prefix}systemctl stop httpd; {$prefix}systemctl start httpd;");
            $ssh->cmd("{$prefix}systemctl enable httpd;");
        }
    }
}

/**
 * @name restartCrond
 * @description restart crond
 * @param SSH $ssh
 * @return 
 */
function restartCrond($version,$ssh,$prefix)
{
    if (is_numeric($version) && in_array($version,array(6,7)))
    {
        if($version == 6)
        {
            $ssh->cmd("{$prefix}service crond restart;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}chkconfig crond on;");
        }
        else
        {
            $ssh->cmd("{$prefix}systemctl stop crond; {$prefix}systemctl start crond;");
            $ssh->cmd("{$prefix}systemctl enable crond;");
        }
    }
}

/**
 * @name uninstallPMTA
 * @description uninstall PMTA
 * @param integer $version
 * @param SSH $ssh
 * @return 
 */
function uninstallPMTA($version,$ssh,$prefix)
{
    if (is_numeric($version) && in_array($version,array(6,7)))
    {
        if($version == 6)
        {
            $ssh->cmd("{$prefix}service pmta stop; {$prefix}service pmtahttp stop;",true) . PHP_EOL;
        }
        else
        {
            $ssh->cmd("{$prefix}systemctl stop pmta; {$prefix}systemctl stop pmtahttp;");
        }
        
        $ssh->cmd("{$prefix}yum remove -y PowerMTA-4.0r8-201209102127.x86_64",true) . PHP_EOL;
        $ssh->cmd("{$prefix}rm -rf /etc/pmta;rm -rf /var/lib/pmta;rm -rf /var/log/pmta;rm -rf /var/spool/pmta;");

        'deleting Directories ... ' . PHP_EOL;

        $ssh->cmd("{$prefix}rm -rf /var/spool/mailtng/;");
        $ssh->cmd("{$prefix}rm -rf /var/spool/pmta/;"); 
    }
}

/**
 * @name restartPMTA
 * @description restart PMTA
 * @param integer $version
 * @param SSH $ssh
 * @return 
 */
function restartPMTA($version,$ssh,$prefix)
{
    if (is_numeric($version) && in_array($version,array(6,7)))
    {
        if($version == 6)
        {
            $ssh->cmd("{$prefix}service pmta restart; {$prefix}service pmtahttp restart;",true) . PHP_EOL;
            $ssh->cmd("{$prefix}chkconfig pmta on; {$prefix}chkconfig pmtahttp on;");
        }
        else
        {
            $ssh->cmd("{$prefix}systemctl stop pmta; {$prefix}systemctl start pmta; {$prefix}systemctl stop pmtahttp; {$prefix}systemctl start pmtahttp;");
            $ssh->cmd("{$prefix}systemctl enable pmta; {$prefix}systemctl enable pmtahttp;");
        }
    }
}

/**
 * @name triggerFatalError
 * @description trigger Fatal Error
 * @param string $errorMessage
 * @param int $serverId
 * @param SSH $ssh
 * @return 
 */
function triggerFatalError($errorMessage,$serverId,$ssh = null)
{
    $errorMessage = strlen($errorMessage) == 0 ? 'Unknown Error !' : $errorMessage;
    
    # set proccess to 1 means completed
    System::executeCommand("'completed' > " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.process');
    
    if($ssh != null)
    {
        # disconnect from the server 
        $ssh->disconnect();
    }
    
    # disconnect from the database
    Database::secureDisconnect();
    
    # print progress message
    die($errorMessage . PHP_EOL);
}

/**
 * @name showProgressMessage
 * @description show Progress Message
 * @param string $message
 * @return 
 */
function showProgressMessage($message)
{ 
    if($message != null && strlen($message) > 0)
    {
        echo '- ' . $message . PHP_EOL;
    }
}