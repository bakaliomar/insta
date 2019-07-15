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
 * @name            worker.php 
 * @description     manage ip emails to send 
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */

# check if the drop file is exixted
$fileName = (count($argv) > 2) ? $argv[1] : null;
$ipId = (count($argv) > 2) ? $argv[2] : null;
$filePath = '/usr/mailtng/tmp/drops/' . $fileName;

# check if the file is already there
if(isset($fileName) && file_exists($filePath))
{
    $status = 'in-progress';
    $interrupted = false;
    
    # uncompress the data that has been sent
    $data = unserialize(gzuncompress(base64_decode(file_get_contents($filePath))));
    
    if($data == null || $data == '')
    {
        die('please check the data you have sent ! it seems that it\'s blank .');
    }

    if($data['is-drop'] == 'true' && intval($data['emails-per-ip']) > 0 && $data['emails-period-value'] > 0)
    {
        $timefrequency = $data['emails-period-value'];
        
        switch ($data['emails-period-type']) 
        {
            case 'minutes':
            {
                $timefrequency = $data['emails-period-value'] * 60;
                break;
            }
            case 'hours':
            {
                $timefrequency = $data['emails-period-value'] * 60 * 60;
                break;
            }
        }

        $emailsFrequencySleep = $timefrequency / intval($data['emails-per-ip']);
    }

    $ip = $data['ips-pickup-files'][$ipId];
    
    foreach ($ip as $pickupFile) 
    {
        # insert the body
        $pickupFile = str_replace('[BODY]',base64_decode($data['body-message']),$pickupFile);
        
        # check if there is a negative 
        if($data['negative-file'] != null && $data['negative-file'] != '')
        {
            $pickupFile = str_replace('[NEGATIVE]',gzuncompress(base64_decode($data['negative-file'])),$pickupFile);
        }
        
        # drop the pickup file to PMTA
        $randString = generateRandomString(10);
        file_put_contents('/var/spool/mailtng/pickup/pickup_msg_' . $data['drop-id'] . '_' . $ipId . '_' . time() . '_' . $randString,$pickupFile);

        if($data['is-drop'] == 'true')
        {
            # check and updates progress 
            $progress = getIpProgressFile($data['drop-id'], $ipId);

            if(array_key_exists('status', $progress[0]))
            {
                if(trim($progress[0]['status']) == 'in-progress')
                {
                    $progress[0]['progress'] = intval($progress[0]['progress']) + 1;

                    updateIpProgressFile($progress[0],$ipId);    
                }
                else
                {
                    $status = $progress['status'];
                    $interrupted = true;
                    break;
                }
            }
        }
        
        # check if there is a timer 
        if(isset($emailsFrequencySleep) && $emailsFrequencySleep > 0)
        {
            msleep(floatval($emailsFrequencySleep));
        }
    }
    
    if($data['is-drop'] == 'true')
    {
        # check if all ips are completed 
        $result = completeDrop($data,$interrupted,$filePath);

        if($result == true)
        {
            die('drop completed !' . PHP_EOL);
        }
    }
}
else
{
    die('drop file does not exist !' . PHP_EOL);
}

