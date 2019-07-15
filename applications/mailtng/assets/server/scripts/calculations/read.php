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
 * @name            read.php 
 * @description     read all accounting files and update the database 
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */
# correct PHP's detection of line endings
ini_set('auto_detect_line_endings', TRUE);

# define the bounces and dellivered paths
$bouncesPath = '/etc/pmta/bounces/';
$deliveredPath = '/etc/pmta/delivered/';

# get the current date 
$today = @date('Y-m-d');
$tomorrow = @date('Y-m-d',  time()+(24*60*60));

$result = array();

# delivery 
$command = "cat /etc/pmta/delivered/moved/$today/d-*.csv";
$content = cmd($command,true);

if(count($content) && count($content['output']))
{
    $logs = array_map('str_getcsv',array_filter($content['output'], function($var) { return (trim($var) != "type,bounceCat,timeLogged,rcpt,dsnAction,dsnStatus,dsnDiag,dlvSourceIp,header_drop_meta"); }));

    if(count($logs))
    {
        foreach ($logs as $row) 
        {
            $dropMeta = array_key_exists(8,$row) ? $row[8] : null;
            
            if($dropMeta != null && !empty($dropMeta))
            {
                if(!empty($dropMeta) && strpos($dropMeta,'|') > -1)
                {
                    $dropMetaArray = explode('|',$dropMeta);
                    
                    if(count($dropMetaArray) == 2)
                    {
                        $dropId = intval($dropMetaArray[0]);
                        $ipId = intval($dropMetaArray[1]);
                        
                        if($dropId > 0 && $ipId > 0)
                        {
                            $old = key_exists($dropId,$result) && key_exists($ipId,$result[$dropId]) && key_exists('delivered',$result[$dropId][$ipId]) ? intval($result[$dropId][$ipId]['delivered']) : 0;
                            $result[$dropId][$ipId]['delivered'] = $old + 1;
                        }
                    }
                }
            }
        } 
    }
}

# bounces 
$command = "cat /etc/pmta/bounces/moved/$today/b-*.csv";
$content = cmd($command,true);

if(count($content) && count($content['output']))
{
    $logs = array_map('str_getcsv',array_filter($content['output'], function($var) { return (trim($var) != "type,bounceCat,timeLogged,rcpt,dsnAction,dsnStatus,dsnDiag,dlvSourceIp,header_drop_meta"); }));

    if(count($logs))
    {
        foreach ($logs as $row) 
        {
            $dropMeta = array_key_exists(8,$row) ? $row[8] : null;
            
            if($dropMeta != null && !empty($dropMeta))
            {
                if(!empty($dropMeta) && strpos($dropMeta,'|') > -1)
                {
                    $dropMetaArray = explode('|',$dropMeta);
                    
                    if(count($dropMetaArray) == 2)
                    {
                        $dropId = intval($dropMetaArray[0]);
                        $ipId = intval($dropMetaArray[1]);
                        
                        if($dropId > 0 && $ipId > 0)
                        {
                            $old = key_exists($dropId,$result) && key_exists($ipId,$result[$dropId]) && key_exists('bounces',$result[$dropId][$ipId]) ? intval($result[$dropId][$ipId]['bounces']) : 0;
                            $result[$dropId][$ipId]['bounces'] = $old + 1;
                        }
                    }
                }
            }
        } 
    }
}

# create queries
if (count($result))
{
    $data = array();

    foreach ($result as $dropId => $row)
    {
        if(count($row))
        {
            foreach ($row as $ipId => $stats) 
            {
                $bounce = key_exists('bounces',$stats) ? intval($stats['bounces']) : 0;
                $delivered = key_exists('delivered',$stats) ? intval($stats['delivered']) : 0;

                $data[] = array(
                    'drop_id' => $dropId,
                    'ip_id' => $ipId,
                    'delivered' => $delivered,
                    'bounced' => $bounce
                );
            }
        } 
    }

    $url = "";
    
    # send the request to mailtng API to complete the drop 
    sendPostRequest($url, array(
        'api_key' => 'x0ja8s4a3duqk9e2w6vga91hrvi7t14wrdxpv754aql055tr2ee2d59b6hop',
        'func_name' => 'calculate_drop',
        'data' => serialize($data)
    ));
}

# finish process
die('Operation Finished!');