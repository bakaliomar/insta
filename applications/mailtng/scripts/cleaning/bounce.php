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
use ma\applications\mailtng\models\admin\Server as Server;
use ma\applications\mailtng\models\data\Fresh as Fresh;
use ma\mailtng\ssh2\SSH as SSH;
use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
/**
 * @name            bounce.php 
 * @description     a native script that cleans bounce
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

# check if the parameters has been sent 
if(count($argv) == 4)
{
    # get all the parameters 
    $proccessId = intval(trim($argv[1]));
    $serverId = intval($argv[2]);
    $listName = base64_decode($argv[3]);
    
    if($serverId > 0 && !empty($listName))
    {
        # connect to the default database 
        Database::secureConnect();
                      
        # get the servers from database
        $server = Server::first(true,array('id = ?',$serverId),array('id','name','main_ip','username','password','ssh_port'));
        
        $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
        $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

        if($sshConnector->isConnected())
        {
            $parts = explode('.',$listName);
            $schema = $parts[0];
            $table = $parts[1];
            $parts2 = explode('_',$table);
            $flag = intval($parts2[count($parts2) - 1]) > 0 ? $parts2[count($parts2) - 2] : $parts2[count($parts2) - 1];
            $flag = strlen($flag) != 2 ? 'us' : $flag;
            
            # bounce
            $command = "cat /etc/pmta/bounces/moved/*/b-*.csv";
            $content = explode(PHP_EOL,$sshConnector->cmd($command,true));

            # clean
            $command = "cat /etc/pmta/delivered/moved/*/d-*.csv";
            $content = array_merge($content,explode(PHP_EOL,$sshConnector->cmd($command,true)));
            
            if(count($content))
            {
                $logs = array_map('str_getcsv',array_filter($content, function($var) { return (trim($var) != "type,bounceCat,timeLogged,rcpt,dsnAction,dsnStatus,dsnDiag,dlvSourceIp,header_x_campaign");}));
                $count = count($logs);
                
                if($count > 0)
                {
                    $index = 1;
                    $status = 'in progress';
                    $emailsindex = 0;
                    $data = json_encode(array('data_list' => $listName,'hard_bounce_emails' => $emailsindex));
                    
                    foreach ($logs as $log) 
                    {
                        # switch to lists database
                        Database::switchToDefaultDatabase();

                        # update progress 
                        $progress = round($index / $count * 100);
                        Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.proccesses SET progress = '{$progress}%' , status = '{$status}' , data = '{$data}' WHERE id = '$proccessId'");

                        # switch to lists database
                        Database::switchToDatabase('lists');
                
                        if(count($log) == 9)
                        {
                            $type = trim($log[1]);

                            if($type == 'hardbnc')
                            {
                                $emailsindex++;
                                $email = trim($log[3]);
                                Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM $listName WHERE email = '$email'");
                                $data = json_encode(array('data_list' => $listName,'hard_bounce_emails' => $emailsindex));  
                            }
                            elseif($type == 'success')
                            {
                                $email = trim($log[3]);
                                Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM $listName WHERE email = '$email'");
                                
                                # check if the table is not there create it
                                if(Database::getCurrentDatabaseConnector()->checkIfTableExists($schema,'clean_'.$flag) == 'false')
                                {
                                    Fresh::synchronizeWithDatabase('clean_'.$flag,$schema);
                                }
                                
                                $query = "INSERT INTO {$schema}.clean_{$flag} SELECT nextval('{$schema}.seq_id_clean_{$flag}'),'$email' WHERE NOT EXISTS (SELECT 1 FROM {$schema}.clean_{$flag} WHERE email='$email')";

                                Database::getCurrentDatabaseConnector()->executeQuery($query); 
                            }
                        }

                        $index++;
                    }
                    
                    if($emailsindex > 0)
                    {
                        $count = Database::getCurrentDatabaseConnector()->query()->from("$listName")->count();
                        
                        if($count == 0)
                        {
                            Database::getCurrentDatabaseConnector()->executeQuery("DROP TABLE $listName CASCADE");
                        }
                    } 
                }
            }

            $sshConnector->disconnect();
        }

        # switch to lists database
        Database::switchToDefaultDatabase();
        $finalStatus = 'interrupted';
        $finishTime = date('Y-m-d H:i:s');
        
        if(isset($status) && $status == 'in progress')
        {
            $finalStatus = 'completed';
        }
        
        # update the proccess
        Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.proccesses SET status = '{$finalStatus}' , finish_time = '{$finishTime}' WHERE id = '$proccessId'");
        
        # disconnect from all databases 
        Database::secureDisconnect();
    }
}
else 
{
    # print progress message
    echo 'Please check the parameters that has been sent to this script !' . PHP_EOL;
}