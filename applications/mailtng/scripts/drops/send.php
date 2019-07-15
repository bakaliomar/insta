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
use ma\applications\mailtng\models\admin\OfferLink as OfferLink;
use ma\applications\mailtng\models\production\Drop as Drop;
use ma\applications\mailtng\models\production\DropIps as DropIps;
use ma\mailtng\ssh2\SSH as SSH;
use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
/**
 * @name            mailing_delivery.php 
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
$dropFileName = (count($argv) > 1) ? $argv[1] : null;

# check if the file is already there
if(isset($dropFileName) && file_exists(ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'drops' . DS . $dropFileName))
{
    # get the file content
    $content = file_get_contents(ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'drops' . DS . $dropFileName);
    
    # parse the file into an array 
    $formData = json_decode($content,true);

    if(count($formData))
    {
        # connect incase if not connected 
        Database::secureConnect();
        
        $drop['proccess-id'] = getmypid();
        $drop['total-emails'] = intval(Arrays::getElement($formData,'server-count',0));
        $drop['ip-emails-count'] = Arrays::getElement($formData,'ip-count',[]);
        $drop['status'] = 'in-progress';
        $drop['start-time'] = date('Y-m-d H:i:s');
        $drop['queries'] = Arrays::getElement($formData,'queries',[]);
        $drop['user-id'] = intval(Arrays::getElement($formData,'user-id',0));
        $drop['is-drop'] = array_key_exists('drop',$formData) ? true : false;
        $drop['server'] = Server::first(true,array('id = ?',intval(Arrays::getElement($formData,'server-id',0))),array('id','name','main_ip','ssh_port','username','password'));
        $drop['ips-emails-proccess'] = Arrays::getElement($formData,'ips-emails-proccess');
        $drop['send-test-after'] = intval(Arrays::getElement($formData,'send-test-after',1000));
        $drop['recipients-emails'] = array_key_exists('recipients-emails',$formData) ? explode(";",Arrays::getElement($formData,'recipients-emails')) : [];
        $drop['auto-response'] = Arrays::getElement($formData,'auto-response','off');
        $drop['batch'] = intval(Arrays::getElement($formData,'batch',100));
        
        if($drop['auto-response'] != null && $drop['auto-response'] != '' && $drop['auto-response'] != 'off')
        {
            $drop['auto-response-rotation'] = intval(Arrays::getElement($formData,'auto-response-frequency',1000));
            $drop['auto-reply-emails'] = array_key_exists('auto-reply-emails',$formData) ? explode("\n", str_replace("\r", "", Arrays::getElement($formData,'auto-reply-emails'))) : [];
            
            if(count($drop['auto-reply-emails']))
            {
                $drop['auto-reply-rotator'] = new Rotator($drop['auto-reply-emails'],$drop['auto-response-rotation']);
            }
        }
        
                    
        # check for server 
        if(count($drop['server']) == 0)
        {
            throw new BackendException('Server does not exist !');
        }
        
        $drop['selected-ips'] = Arrays::getElement($formData,'ips');
        
        if(count($drop['selected-ips']) == 0)
        {
            throw new BackendException('No Ips Selected !');
        }
            
        $drop['ips'] = Ip::all(true,array('id IN('.implode(',',$drop['selected-ips']).')',''),array('id','value','rdns'));
        
        if(count($drop['ips']) == 0)
        {
            throw new BackendException('No Ips Selected !');
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
        $drop['links'] = OfferLink::all(true,['creative_id = ?',$drop['creative-id']]);
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

        # calculate total sent and store the drop object with its drop ips into the database 
        if($drop['is-drop'] == true)
        {
            # save the drop into the database
            $dropObject = new Drop(array(
                "user_id" => $drop['user-id'],
                "server_id" => $drop['server']['id'],
                "isp_id" => $drop['isp-id'],
                "status" => $drop['status'],
                "start_time" => $drop['start-time'],
                "total_emails" => $drop['total-emails'], 
                "sent_progress" => 0,
                "offer_id" => $drop['offer-id'],
                "offer_from_name_id" => intval(Arrays::getElement($formData,'from-name-id')),
                "offer_subject_id" => intval(Arrays::getElement($formData,'subject-id')),
                "recipients_emails" => implode(',',$drop['recipients-emails']),
                "pids" => $drop['proccess-id'],
                "header" => base64_encode($drop['headers'][0]),
                "creative_id" => $drop['creative-id'],
                "lists" => $drop['lists'],
                "post_data" => base64_encode($content)
            ));
            
            $drop['drop-id'] = $dropObject->save();
            
            # save drop ips
            foreach ($drop['ips'] as $ip) 
            {
                $totalIp = $drop['ip-emails-count'][intval($ip['id'])];
                
                # store drop ips into the database
                $ipObject = new DropIps(array(
                    "server_id" => $drop['server']['id'],
                    "isp_id" => $drop['isp-id'],
                    "drop_id" => $drop['drop-id'],
                    "ip_id" => intval($ip['id']),
                    "drop_date" => $drop['start-time'],
                    "total_sent" => $totalIp,
                    "delivered" => 0,
                    "bounced" => 0
                ));

                $ipObject->save();
            }
        }

        # connect to the server
        if(filter_var($drop['server']['main_ip'],FILTER_VALIDATE_IP))
        {
            $ssh = new SSH($drop['server']['main_ip'],new SSHPasswordAuthentication($drop['server']['username'],$drop['server']['password']),$drop['server']['ssh_port']);

            if(!$ssh->isConnected())
            {
                throw new BackendException('Could not connect to server !');
            }

            if($drop['is-drop'] == true)
            {
                // ips rotation case 
                if($drop['ips-emails-proccess'] == 'ips-rotation')
                {
                    $drop['available-random-tags'] = gatherRandomTags($content);
        
                    if(Arrays::getElement($formData,'upload-images') == 'on')
                    {
                        # repleace images in body by our links 
                        replaceAndUploadImages($drop);
                    }
        
                    $drop['x-delay'] = intval(Arrays::getElement($formData,'x-delay',1));

                    # create an ip rotator
                    $drop['ips-rotation'] = intval(Arrays::getElement($formData,'ips-rotation',1));
                    $drop['ips-rotator'] = new Rotator($drop['ips'],$drop['ips-rotation']);

                    # switch to lists database
                    Database::switchToDatabase('lists');

                    # construct emails list
                    $emails = [];

                    foreach ($drop['queries'] as $query)
                    {
                        $emails = array_merge($emails, Database::getCurrentDatabaseConnector()->executeQuery($query,true));
                    }

                    # switch to default database
                    Database::switchToDefaultDatabase();
                    
                    $emailsCount = count($emails);

                    if ($emailsCount)
                    {
                        $index = 0;
                        $MailMergeSections = array();
                        foreach ($emails as $client)
                        {
                            $ip = $drop['ips-rotator']->getCurrentValue();
                            //$email = preg_replace( "/\r|\n/","", trim($client['email']));
                            
                            //if(filter_var($email,FILTER_VALIDATE_EMAIL))
                            //{
                                $MailMergeSections[] = buildMailMergeSection($drop, $ip, $client);
                            //}
                            
                            if(($index % intval($drop['batch'])) == 0 || $index == ($emailsCount -1))
                            {
                                $pickup= 'pickup_message_' . Strings::generateRandomText(8,true,true,true,false);
                                $ssh->scp('send',array('/home/'.$pickup),createPickupContent($drop, $ip,$MailMergeSections));
                                $ssh->cmd('mv /home/'.$pickup . ' /var/spool/mailtng/pickup/');
                                $drop['ips-rotator']->rotate();
                                $MailMergeSections = array(); 
								# build the query
                            $query = "UPDATE production.drops SET sent_progress = '$index' WHERE id = {$drop['drop-id']}";

                            # execute the query 
                            $result = Database::getCurrentDatabaseConnector()->executeQuery($query,true);
                            }

                            # test after case 
                            if($index > 0 && $index % $drop['send-test-after'] == 0)
                            {
                                foreach ($drop['recipients-emails'] as $rcpt)
                                {
                                    $ssh->scp('send',array('/var/spool/mailtng/pickup/pickup_message_' . Strings::generateRandomText(8,true,true,true,false)),createPickupContent($drop, $ip,buildMailMergeSection($drop,$ip,['id' => 0,'table' => '','email' => $rcpt])));
                                }
                            }

                            $index++;
                            $drop['ips-rotator']->rotate();
                            
                            # wait for a while 
                            sleep($drop['x-delay']);
                            
                            # build the query
                           // $query = "UPDATE production.drops SET sent_progress = '$index' WHERE id = {$drop['drop-id']}";

                            # execute the query 
                           // $result = Database::getCurrentDatabaseConnector()->executeQuery($query,true);
                        }
                        
						
                        # build the query
                        $status = 'completed';
                        $finishTime = date('Y-m-d H:i:s');
                        $query = "UPDATE production.drops SET status = '$status' , finish_time = '$finishTime' , sent_progress = '$index' WHERE id = {$drop['drop-id']}";

                        # execute the query 
                        $result = Database::getCurrentDatabaseConnector()->executeQuery($query,true);
                    }
                }
                else 
                {
                    # executing the script that handles mailing emails
                    $scriptPath = Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'drops' . DS . 'worker.php'; 
                    
                    foreach ($drop['ips'] as $ip)
                    {
                        exec("nohup php {$scriptPath} {$drop['drop-id']} {$ip['id']} {$dropFileName} > /tmp/worker_{$drop['drop-id']}_{$ip['id']}.log &",$arr);
                    }
                }
            } 
            else
            { 
                $drop['available-random-tags'] = gatherRandomTags($content);
                if(Arrays::getElement($formData,'upload-images') == 'on')
                {
                    # repleace images in body by our links 
                    replaceAndUploadImages($drop);
                }

                foreach ($drop['ips'] as $ip)
                {
                    foreach ($drop['recipients-emails'] as $rcpt)
                    {
                        $ssh->scp('send',array('/var/spool/mailtng/pickup/pickup_message_' . Strings::generateRandomText(8,true,true,true,false)),createPickupContent($drop, $ip,buildMailMergeSection($drop,$ip,['id' => 0,'table' => '','email' => $rcpt])));
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