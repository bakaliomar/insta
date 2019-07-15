<?php
/**
 * @framework       MailTng Framework
 * @version         1.1
 * @author          MailTng Team
 * @copyright       Copyright (c) 2015 - 2016.	
 * @license		
 * @link	
 */ 
require_once '/usr/mailtng/scripts/helpers/main.php';
/**
 * @name            main.php 
 * @description     send emails using ips by a timer 
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */
# check if the drop file is exixted
$fileName = (count($argv) > 1) ? $argv[1] : null;
$filePath = '/usr/mailtng/tmp/drops/' . $fileName;
$ipWorkerFile = '/usr/mailtng/scripts/drops/worker.php';
$logDirectory = '/usr/mailtng/tmp/logs';


# check if the file is already there
if(isset($fileName) && file_exists($filePath))
{
    # uncompress the data that has been sent
    $data = unserialize(gzuncompress(base64_decode(file_get_contents($filePath))));
    
    if($data == null || $data == '')
    {
        die('please check the data you have sent ! it seems that it\'s blank .' . PHP_EOL);
    }

    # start ditributing the process between ips 
    $ips = array_keys($data['ips-pickup-files']);
    
    if (count($ips))
    {
        foreach ($ips as $ipId) 
        {
            if($data['is-drop'] == 'true')
            {
                # create the progress file 
                createIpProgressFile($data['drop-id'],$data['drop-status'],$data['drop-total'],count($data['ips-pickup-files'][$ipId]),$ipId);
            }
            else
            {
                $data['drop-id'] = 0;
            }
            
            $logFile = "{$logDirectory}/message_log_{$data['drop-id']}.log";
            
            # execute the ip worker 
            exec("nohup php {$ipWorkerFile} {$fileName} {$ipId} > $logFile 2>&1 &");
        }
    }
}
else
{
    die('drop file does not exist !' . PHP_EOL);
}