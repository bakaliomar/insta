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
use ma\mailtng\files\Paths as Paths;
use ma\mailtng\encryption\Crypto as Crypto;
use ma\mailtng\types\Arrays as Arrays;
use ma\applications\mailtng\models\data\DataList as DataList;
use ma\applications\mailtng\models\production\Drop as Drop;
use ma\applications\mailtng\models\admin\Offer as Offer;
use ma\applications\mailtng\models\statistics\Open as Open;
use ma\applications\mailtng\models\statistics\Click as Click;
use ma\applications\mailtng\models\statistics\Unsub as Unsub;
/**
 * @name            actions.php 
 * @description     tracking actions
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */

# to ensure scripts are not called from outside of the framework 
define('MAILTNG_FMW',true);  

# rootPath
$rootPath = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

# require the main configuration of the framework 
require_once $rootPath . '/configs/init.conf.php';

# require request init configurations ( application init and database , cache ... )
require_once $rootPath . '/configs/request.init.conf.php';

# require the helper
require_once Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'tracking' . DS . 'helper.php';

# gather parameters
$agent = (count($argv) > 1) ? base64_decode($argv[1]) : null;
$ip = (count($argv) > 2) ? $argv[2] : null;
$language = (count($argv) > 3) ? $argv[3] : null;
$parameters = (count($argv) > 4) ? Crypto::AESDecrypt(trim($argv[4])) : null;
$message = (count($argv) > 5) && !empty(base64_decode($argv[5])) ? $argv[5] : base64_encode('No Message!');

# check if the parameters are exixted
if(isset($agent) && isset($ip) && !empty($parameters) && strpos($parameters,'|') > -1)
{
    # connect to the database
    Database::secureConnect();
    
    # convert values into an array
    $parameters = explode('|',$parameters);

    # get operation Type 
    $type = Arrays::getElement($parameters,0);

    # get the email
    $dropId = intval(Arrays::getElement($parameters,1));
    $clientId = intval(Arrays::getElement($parameters,3));
    $schema = trim(Arrays::getElement(explode('.',Arrays::getElement($parameters,2)),0));
    $table = $existingTable = trim(Arrays::getElement(explode('.',Arrays::getElement($parameters,2)),1));
    $tableType = $existingTableType = trim(Arrays::getElement(explode('_',$table),0));
    $existingFlag = trim(Arrays::getElement(explode('_',$table),1));
    $tableName = str_replace($tableType . '_' . $existingFlag . '_','', $table);
    $email = trim(preg_replace('/\s\s+/','',Arrays::getElement($parameters,4)));
    $removeRecord = true;

    if(filter_var($email,FILTER_VALIDATE_EMAIL) && $clientId != 0 && strpos($table,'seeds') === false)
    {
        # get browser meta info 
        $metaInfo = getClientMetadata($agent, $ip, $language);

        $actionInfo = array(
            'drop_id' => $dropId,
            'email' => $email,
            'action_date' => date('Y-m-d H:i:s'),
            'list' => $table,
            'ip' => $ip,
            'country' => $metaInfo['country'],
            'region' => $metaInfo['region'],
            'city' => $metaInfo['city'],
            'language' => $metaInfo['language'],
            'device_type' => $metaInfo['device-type'],
            'device_name' => $metaInfo['device-name'],
            'os' => $metaInfo['os'],
            'browser_name' => $metaInfo['browser-name'],
            'browser_version' => $metaInfo['browser-version']
        );

        # register the action first ( click / open / unsub )
        switch (trim($type)) 
        {
            case 'open':
            {
                $count = Database::getCurrentDatabaseConnector()->query()->from('stats.opens')->where('drop_id = ? AND email = ?',array($dropId,$email))->count();
                
                if($count == 0)
                {
                    $openObject = new Open($actionInfo);
                    $openObject->save();
                }
                
                break;
            }
            case 'preview': # click 
            {
                $count = Database::getCurrentDatabaseConnector()->query()->from('stats.clicks')->where('drop_id = ? AND email = ?',array($dropId,$email))->count();
                
                if($count == 0)
                {
                    $clickObject = new Click($actionInfo);
                    $clickObject->save();
                }
                
                break;
            }
            case 'unsub':
            case 'srv_unsub':
            {
                $count = Database::getCurrentDatabaseConnector()->query()->from('stats.unsubs')->where('drop_id = ? AND email = ?',array($dropId,$email))->count();
                
                if($count == 0)
                {
                    $actionInfo['type'] = (trim($type) == 'srv_unsub') ? 'srv_unsub' : 'offer_unsub';
                    $actionInfo['message'] = $message;
                    $unsubObject = new Unsub($actionInfo);
                    $unsubObject->save();
                }
                
                break;
            }
        }
        
        $flag = getFlag($metaInfo['country']);
            
        # switch to the lists database 
        Database::switchToDatabase('lists');

        # get the email 
        $result = array();

        if(Database::getCurrentDatabaseConnector()->checkIfTableExists($schema,$table) == 'true')
        {
            $result = Database::getCurrentDatabaseConnector()->query()->from("$schema.$table")->where('id = ?',$clientId)->first();
        }

        # if the email has been already moved to another table
        if(empty($result))
        {
            $result = getClientResult($email,$schema,$existingTable,$existingFlag,$existingTableType);
        }

        # if the email was found
        if(count($result))
        {
            # switch back to default database 
            Database::switchToDefaultDatabase();

            $verticals = array();

            # get verticals ids 
            $drop = Drop::first(true,array('id = ?',$dropId),array('id','offer_id','user_id','isp_id'));

            if(count($drop))
            {
                $offer = Offer::first(true,array('id = ?',$drop['offer_id']));
                
                $verticals = array($offer['vertical_id']);

                # add verticals 
                if(key_exists('verticals',$result) && $result['verticals'] != 'NULL' && strlen($result['verticals']) > 0)
                {
                    $verticals = array_unique(array_merge($verticals,explode(',',$result['verticals'])));
                }

                $verticals = (count($verticals) > 0) ? implode(',',$verticals) : 'NULL';
                
                # switch back to lists database 
                Database::switchToDatabase('lists');

                # initialize the client object
                $client = getClientObject($existingTableType,$type);
                
                # check if the client object has been initialized
                if($client != null)
                {
                    $client->setTable($client->getTable() . '_' . $flag . '_' . $tableName);
                    $client->setSchema($schema); 

                    # check if the table is not there create it
                    if(Database::getCurrentDatabaseConnector()->checkIfTableExists($client->getSchema(),$client->getTable()) == 'false')
                    {
                        $client->synchronizeWithDatabase($client->getTable(),$client->getSchema());
                        
                        # switch to default database
                        Database::switchToDefaultDatabase();

                        # create the list
                        $list = new DataList();
                        $list->setName("{$client->getSchema()}.{$client->getTable()}");
                        $list->setIsp_id(intval(Arrays::getElement($drop,'isp_id',1)));
                        $list->setFlag($flag);
                        $list->setCreated_by(intval(Arrays::getElement($drop,'user_id',1)));
                        $list->setCreated_at(date("Y-m-d"));
                        $list->setLast_updated_by(intval(Arrays::getElement($drop,'user_id',1)));
                        $list->setLast_updated_at(date("Y-m-d"));
                        $list->save();
                        
                        # switch back to lists database 
                        Database::switchToDatabase('lists');
                    }
                    
                    if(in_array(trim($type),array('open','unsub')))
                    {
                        # if the table
                        if(in_array(trim($existingTableType),array('openers','clickers','leads')) && $flag == $existingFlag)
                        {
                            $client->setId($result['id']);
                            $client->load();  
                            $removeRecord = false;
                        }
                    }
                    else if(trim($type) == 'preview')
                    {
                        # if the table
                        if(in_array(trim($existingTableType),array('clickers','leads')) && $flag == $existingFlag)
                        {
                            $client->setId($result['id']);
                            $client->load();  
                            $removeRecord = false;
                        }
                    }                           
  
                    $client->setEmail($email);   
                    $client->setAction_date(date('Y-m-d H:i:s'));
                    $client->setVerticals($verticals);
                    $client->setIp($ip);
                    $client->setAgent(base64_encode($agent));
                    $client->setCountry($metaInfo['country']);
                    $client->setRegion($metaInfo['region']);
                    $client->setCity($metaInfo['city']);
                    $client->setLanguage($metaInfo['language']);
                    $client->setDevice_type($metaInfo['device-type']);
                    $client->setDevice_name($metaInfo['device-name']);
                    $client->setOs($metaInfo['os']);
                    $client->setBrowser_name($metaInfo['browser-name']);
                    $client->setBrowser_version($metaInfo['browser-version']);
                    
                    if(trim($type) == 'srv_unsub')
                    {
                        $client->setMessage($message);
                        $client->setDrop_id($dropId);
                    }
                    elseif(trim($type) == 'unsub')
                    {
                        $offers = array(trim($drop['offer_id']));

                        # add offers excluded  
                        if(key_exists('offers_excluded',$result) && $result['offers_excluded'] != 'NULL' && strlen($result['offers_excluded']) > 0)
                        {
                            $offers = array_unique(array_merge($offers,explode(',', trim($result['offers_excluded'],','))));
                        }

                        $offers = (count($offers) > 0) ? ',' . implode(',',$offers) . ',' : 'NULL';
                        $client->setOffers_excluded($offers);
                    }

                    # save the client 
                    $client->save();

                    # check if we have to delete the old record
                    if($removeRecord == true)
                    {
                        Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM {$schema}.{$existingTable} WHERE email = '$email'");

                        $count = Database::getCurrentDatabaseConnector()->query()->from("{$schema}.{$existingTable}")->count();
                        
                        if($count == 0)
                        {
                            Database::getCurrentDatabaseConnector()->executeQuery("DROP TABLE {$schema}.{$existingTable} CASCADE");
                            Database::getCurrentDatabaseConnector()->executeQuery("DROP SEQUENCE {$schema}.seq_id_{$existingTable} CASCADE");
                            
                            # switch to default database 
                            Database::switchToDefaultDatabase();
                                  
                            # drop the list 
                            Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.data_lists WHERE name='{$schema}.{$existingTable}'",true);
                        }
                    }
                } 
            } 
        }
        else
        {
            die(json_encode(array('status' => 500,'message' => 'email not found !')));
        }
    }
    
    # disconnect from db 
    Database::secureDisconnect();
    
    # finish the proccess 
    die(json_encode(array('status' => 200,'message' => 'tracking finished successfully !')));
}
else
{
    die(json_encode(array('status' => 500,'message' => 'missing parameters !')));
}