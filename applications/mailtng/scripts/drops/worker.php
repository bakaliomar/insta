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
use ma\mailtng\types\Strings as Strings;
use ma\mailtng\files\Paths as Paths;
use ma\mailtng\types\Arrays as Arrays;
use ma\mailtng\pmta\Rotator as Rotator;
use ma\mailtng\encryption\Crypto as Crypto;
use ma\mailtng\exceptions\types\BackendException as BackendException;
use ma\applications\mailtng\models\admin\Server as Server;
use ma\applications\mailtng\models\admin\Ip as Ip;
use ma\applications\mailtng\models\admin\OfferFromName as OfferFromName;
use ma\applications\mailtng\models\admin\OfferSubject as OfferSubject;
use ma\mailtng\ssh2\SSH as SSH;
use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
/**
 * @name            worker.php 
 * @description     a native script that prepares the pickup files and send them to the appropriate vps server 
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

# require mailing delivery helper
require_once Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'drops' . DS . 'helper.php';

# read the drop generated file
$drop = array();

# check if the drop file is exixted
$dropId = (count($argv) > 1) ? intval($argv[1]) : null;
$ipId = (count($argv) > 2) ? intval($argv[2]) : null;
$dropFileName = (count($argv) > 3) ? $argv[3] : null;

# check if the file is already there
if($dropId > 0 && $ipId > 0 && isset($dropFileName) && file_exists(ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'drops' . DS . $dropFileName))
{
    # get the file content
    $content = file_get_contents(ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'drops' . DS . $dropFileName);
    
    # parse the file into an array 
    $formData = json_decode($content,true);

    if(count($formData))
    {
        # connect incase if not connected 
        Database::secureConnect();
        
        $drop['drop-id'] = $dropId;
        $workerProccessId = getmypid();
        $drop['queries'] = Arrays::getElement($formData,'queries',[]);
        $drop['user-id'] = intval(Arrays::getElement($formData,'user-id',0));
        $drop['is-drop'] = array_key_exists('drop',$formData) ? true : false;
        $drop['server'] = Server::first(true,array('id = ?',intval(Arrays::getElement($formData,'server-id',0))),array('id','name','main_ip','ssh_port','username','password'));
        $drop['ip'] = Ip::first(true,array('id = ?',$ipId),array('id','value','rdns')); 
        $drop['send-test-after'] = intval(Arrays::getElement($formData,'send-test-after',1000));
        $drop['recipients-emails'] = array_key_exists('recipients-emails',$formData) ? explode("\n", str_replace("\r", "", Arrays::getElement($formData,'recipients-emails'))) : [];
        $drop['auto-response'] = Arrays::getElement($formData,'auto-response','off');
        $drop['emails-per-ip'] = Arrays::getElement($formData,'number-of-emails',0);
        $drop['emails-period-type'] = Arrays::getElement($formData,'emails-period-type','seconds');
        $drop['emails-period-value'] = Arrays::getElement($formData,'emails-period-value',0);
        
        # add pid to the drop 
        Database::getCurrentDatabaseConnector()->executeQuery("UPDATE production.drops SET pids = COALESCE(pids,'') || ',$workerProccessId' WHERE id = {$drop['drop-id']}");
                    
        if($drop['auto-response'] != null && $drop['auto-response'] != '' && $drop['auto-response'] != 'off')
        {
            $drop['auto-response-rotation'] = intval(Arrays::getElement($formData,'auto-response-frequency',1000));
            $drop['auto-reply-emails'] = array_key_exists('auto-reply-emails',$formData) ? explode("\n", str_replace("\r", "", Arrays::getElement($formData,'auto-reply-emails'))) : [];
            
            if(count($drop['auto-reply-emails']))
            {
                $drop['auto-reply-rotator'] = new Rotator($drop['auto-reply-emails'],$drop['auto-response-rotation']);
            }
        }
        
        # advertiser's part
        $drop['sponsor-id'] = intval(Arrays::getElement($formData,'sponsor',0));
        $drop['offer-id'] = intval(Arrays::getElement($formData,'offer',0));
        $drop['from-name'] = (Arrays::getElement($formData,'from-name-text') != '') ? ['id' => intval(Arrays::getElement($formData,'from-name-id')), 'value' => Arrays::getElement($formData,'from-name-text')] : OfferFromName::first(true,array('id = ?',intval(Arrays::getElement($formData,'from-name-id'))));
        $drop['subject'] = (Arrays::getElement($formData,'subject-text') != '') ? ['id' => intval(Arrays::getElement($formData,'subject-id')), 'value' => Arrays::getElement($formData,'subject-text')] : OfferSubject::first(true,array('id = ?',intval(Arrays::getElement($formData,'subject-id'))));
        $drop['creative-id'] = intval(Arrays::getElement($formData,'creative',0));
        $drop['link-encoding'] = Arrays::getElement($formData,'link-encoding','default');  
        
        # message header part
        $drop['headers'] = Arrays::getElement($formData,'headers');
        $drop['headers-rotation'] = intval(Arrays::getElement($formData,'headers-rotation',1));
        $drop['headers-rotator'] = new Rotator($drop['headers'],$drop['headers-rotation']);
        $drop['from-email'] = Arrays::getElement($formData,'from-email');
        $drop['bounce-email'] = Arrays::getElement($formData,'bounce-email');
        $drop['return-path'] = Arrays::getElement($formData,'return-path');
        $drop['reply-to'] = Arrays::getElement($formData,'reply-to');
        $drop['to'] = Arrays::getElement($formData,'to');
        $drop['received'] = Arrays::getElement($formData,'received');   
            
        # body and body placeholders part 
        $drop['message-body'] = Arrays::getElement($formData,'body');
        $drop['negative'] = Arrays::getElement($formData,'negative');
        $drop['content-transfer-encoding'] = Arrays::getElement($formData,'content-transfer-encoding');
        $drop['content-type'] = Arrays::getElement($formData,'content-type');
        $drop['charset'] = Arrays::getElement($formData,'charset');
        $drop['body-placeholders'] = explode(PHP_EOL,Arrays::getElement($formData,'body-placeholders'));
        $drop['body-placeholders-rotation'] = intval(Arrays::getElement($formData,'placeholders-rotation',1));
        $drop['body-placeholders-rotator'] = new Rotator($drop['body-placeholders'],$drop['body-placeholders-rotation']);
  
        # data part
        $drop['track-opens'] = Arrays::getElement($formData,'track-opens');
        $drop['country'] = strtoupper(Arrays::getElement($formData,'country'));
        $drop['isp-id'] = intval(Arrays::getElement($formData,'isp-id',1));
        $drop['lists'] = implode(',',array_map(function($value){ return Crypto::AESDecrypt($value); },Arrays::getElement($formData,'lists')));

        $drop['available-random-tags'] = gatherRandomTags($content);

        # connect to the server
        if(filter_var($drop['server']['main_ip'],FILTER_VALIDATE_IP))
        {
            $ssh = new SSH($drop['server']['main_ip'],new SSHPasswordAuthentication($drop['server']['username'],$drop['server']['password']),$drop['server']['ssh_port']);

            if(!$ssh->isConnected())
            {
                throw new BackendException('Could not connect to server !');
            }
            
            if(Arrays::getElement($formData,'upload-images') == 'on')
            {
                # repleace images in body by our links 
                replaceAndUploadImages($drop);
            }

            $emailsFrequencySleep = 0;
            
            if(intval($drop['emails-per-ip']) > 0 && intval($drop['emails-period-value']) > 0)
            {
                $timefrequency = $drop['emails-period-value'];

                switch ($drop['emails-period-type']) 
                {
                    case 'minutes':
                    {
                        $timefrequency = $drop['emails-period-value'] * 60;
                        break;
                    }
                    case 'hours':
                    {
                        $timefrequency = $drop['emails-period-value'] * 60 * 60;
                        break;
                    }
                }

                $emailsFrequencySleep = $timefrequency / intval($drop['emails-per-ip']);
            }

            Database::switchToDatabase('lists');
            
            # construct emails list
            $emails = [];
            $query = Arrays::getElement($drop['queries'],$ipId,'');

            if($query != '')
            {
                $emails = Database::getCurrentDatabaseConnector()->executeQuery($query,true);
            }

            # switch to default database
            Database::switchToDefaultDatabase();

            $emailsCount = count($emails);
            
            if ($emailsCount > 0)
            {
                $index = 0;
                
                foreach ($emails as $client)
                {
                    $email = preg_replace( "/\r|\n/","", trim($client['email']));
                            
                    if(filter_var($email,FILTER_VALIDATE_EMAIL))
                    {
                        $pickupContent = createPickupContent($drop,$drop['ip'],buildMailMergeSection($drop,$drop['ip'],$client));
                        $ssh->scp('send',array('/var/spool/mailtng/pickup/pickup_message_' . $dropId . '_' . $ipId . '_' . Strings::generateRandomText(8,true,true,true,false)),$pickupContent);
                    }

                    # test after case 
                    if($index > 0 && $index % $drop['send-test-after'] == 0)
                    {
                        foreach ($drop['recipients-emails'] as $rcpt)
                        {
                            $pickupContent = createPickupContent($drop,$drop['ip'],buildMailMergeSection($drop,$drop['ip'],['id' => 0,'table' => '','email' => $rcpt]));
                            $ssh->scp('send',array('/var/spool/mailtng/pickup/pickup_message_' . $dropId . '_' . $ipId . '_' . Strings::generateRandomText(8,true,true,true,false)),$pickupContent);
                        }
                    }

                    $index++;
                            
                    # check if there is a timer 
                    if(isset($emailsFrequencySleep) && $emailsFrequencySleep > 0)
                    {
                        usleep(floatval($emailsFrequencySleep) * 1000000);
                    }
                    
                    # build the query
                    $query = "UPDATE production.drops SET sent_progress = sent_progress + 1 WHERE id = {$drop['drop-id']}";

                    # execute the query 
                    Database::getCurrentDatabaseConnector()->executeQuery($query);
                }
                
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT total_emails,sent_progress FROM production.drops WHERE id = {$drop['drop-id']}");
                
                if(count($result))
                {
                    $total = intval($result[0]['total_emails']);
                    $progress = intval($result[0]['sent_progress']);
                    
                    if($total <= $progress)
                    {
                        # build the query
                        $status = 'completed';
                        $finishTime = date('Y-m-d H:i:s');
                        $query = "UPDATE production.drops SET status = '$status' , finish_time = '$finishTime' WHERE id = {$drop['drop-id']}";

                        # execute the query 
                        Database::getCurrentDatabaseConnector()->executeQuery($query);
                    }
                } 
            }
            
            $ssh->disconnect();
        }
        
        # disconnect from db 
        Database::secureDisconnect();
    }
}
else
{
    throw new BackendException('drop file does not exist !');
}