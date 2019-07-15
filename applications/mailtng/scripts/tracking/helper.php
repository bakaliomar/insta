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
use ma\applications\mailtng\models\data\Opener as Opener;
use ma\applications\mailtng\models\data\Clicker as Clicker;
use ma\applications\mailtng\models\data\Lead as Lead;
use ma\applications\mailtng\models\data\Unsubscriber as Unsubscriber;
use ma\mailtng\http\Client as Client;
use ma\mailtng\types\Arrays as Arrays;
/**
 * @name            helper.php 
 * @description     a helper script that contains some usefull methods 
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */

/**
 * @name getClientMetadata
 * @description get client metadata
 * @param String $userAgent
 * @param String $ip
 * @param String $language
 * @return Array
 */
function getClientMetadata($userAgent,$ip,$language)
{
    $client = new Client();
    $client->reset();
    $client->setAgent($userAgent);
    $client->setIp($ip);
    $client->checkIpMetaInformation();
    $client->checkOS();
    $client->checkBrowsers();
    $client->checkDeviceName();
    $client->setLanguage($language);
    return $client->retreiveInfo();
}

/**
 * @name getFlag
 * @description get client flag
 * @param String $userAgent
 * @param String $country
 * @return String
 */
function getFlag($country)
{
    $europeCountries = array('AUSTRIA','GERMANY','POLAND','CYPRUS','FRANCE','CZECH REPUBLIC','LUXEMBOURG','ITALY','SWEDEN','SPAIN','UKRAINE','DENMARK','HUNGARY','BELGIUM','NETHERLANDS','PORTUGAL','ROMANIA','FINLAND','BULGARIA','NORWAY');

    if(strtoupper($country) == 'UNITED STATES')
    {
        return 'us';
    }
    else if(strtoupper($country) == 'UNITED KINGDOM')
    {
        return 'uk';
    }
    else if(strtoupper($country) == 'Australia')
    {
        return 'au';
    }
    else if(strtoupper($country) == 'Canada')
    {
        return 'ca';
    }
    else if(in_array(strtoupper($country),$europeCountries))
    {
        return 'eu';
    }
    else 
    {
        return 'ot';
    }
}

/**
 * @name getClientObject
 * @description get client object
 * @param String $tableType
 * @param String $type
 * @return String
 */
function getClientObject($tableType,$type)
{
    if($type == 'open' || $type == 'unsub')
    {
        if(strpos($tableType,'leads') > -1)
        {  
            return new Lead();
        }
        else if(strpos($tableType,'clickers') > -1)
        {
            return new Clicker();
        }
        else
        {
            return new Opener();
        }
    }
    else if($type == 'preview')
    {
        if(strpos($tableType,'leads') > -1)
        {  
            return new Lead();
        }
        else
        {
            return new Clicker();
        }
    }
    else if($type == 'lead')
    {
        return new Lead();
    }
    else if($type == 'srv_unsub')
    {
        return new Unsubscriber();
    }
    
    return null;
}

/**
 * @name getClientResult
 * @description get client result
 * @param String $email
 * @param String $schema
 * @param String $existingTable
 * @return String
 */
function getClientResult($email,$schema,&$existingTable,&$existingFlag,&$existingTableType)
{
    $result = array();
    $tables = Database::getCurrentDatabaseConnector()->getAvailableTables($schema);
    
    foreach ($tables as $table) 
    {
        if(strpos($table,'openers_') == 0 || strpos($table,'clickers_') == 0 || strpos($table,'leads_') == 0 || strpos($table,'unsubscribers_') == 0)
        {
            $result = Database::getCurrentDatabaseConnector()->query()->from("{$schema}.{$table}")->where('email = ?',$email)->first();

            if(count($result))
            {
                $existingTable = $table;
                $existingFlag = trim(Arrays::getElement(explode('_',$table),1));
                
                if(substr($table, 0, strlen('openers_')) == 'openers_')
                {
                    $type = "openers";
                }
                elseif(substr($table, 0, strlen('clickers_')) == 'clickers_')
                {
                    $type = "clickers";
                }
                elseif(substr($table, 0, strlen('leads_')) == 'leads_')
                {
                    $type = "leads";
                }
                elseif(substr($table, 0, strlen('unsubscribers_')) == 'unsubscribers_')
                {
                    $type = "unsubscribers";
                }
                
                $existingTableType = $type;
                return $result;
            }
        }
    }
  
    return $result;
}