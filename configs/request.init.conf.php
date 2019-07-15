<?php if(!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
/**
 * @framework       MailTng Framework
 * @version         1.1
 * @author          MailTng Team
 * @copyright       Copyright (c) 2015 - 2016.	
 * @license		
 * @link
 */
use ma\mailtng\registry\Packager as Packager;
use ma\mailtng\configuration\Configuration as Configuration;
use ma\mailtng\application\Application as Application;
use ma\mailtng\database\Database as Database;
use ma\mailtng\caching\Cache as Cache;
use ma\mailtng\types\Arrays as Arrays;
use ma\mailtng\security\Security as Security;
use ma\mailtng\exceptions\types\ApplicationException as ApplicationException;
use ma\mailtng\exceptions\types\DatabaseException as DatabaseException;
/**
 * @name            request.init.conf.php 
 * @description     Init request configuration file that contains request configuration
 * @package         .
 * @category        Config File
 * @author          MailTng Team			
 */
# load and initialize the Configuration class
$configuration = new Configuration(array( "type" => "ini" ));
$configuration = $configuration->initialize();

# store the configuration object in the registry
Packager::set("configuration", $configuration);

# check if a Project folder has been defined but the framework is directly under the root path of www
if(defined('PROJECT_FOLDER') && PROJECT_FOLDER != '' && strpos(ROOT_PATH,PROJECT_FOLDER) === false)
{
    die('<pre><h3>MailTng Framework Error Reporting :</h3>There is something wrong with your configuration <span style="color:red">(You have defined a project folder name but the framework is installed directly under the root path of www)</span></pre>');
}

# getting the application prefix from the url 
$appPrefix = (isset($appPrefix) && $appPrefix != null) ? $appPrefix : Application::getPrefix();

# check if there is a default application defined
if(defined('DEFAULT_APPLICATION_PREFIX') && DEFAULT_APPLICATION_PREFIX != '' && Security::applicationExists(DEFAULT_APPLICATION_PREFIX))
{
    $appPrefix =  DEFAULT_APPLICATION_PREFIX;
}

# check if the application exists
if(!Security::applicationExists($appPrefix))
{
    throw new ApplicationException('Application not found',404);
}

#declaring a variable that contains the application's folder path
$appFolder = APPS_FOLDER. DS . $appPrefix; 

# retrieves all the config defined for the called app 
$initConfig =  $configuration->parse($appFolder . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'application');

# check if no config retrieved 
if(!isset($initConfig))
{
    throw new ApplicationException('No configuration retrieved for the called application , please check you application config files');
}

# creates a new application instance 
$application = new Application(array(
    'name' => $initConfig->name,
    'prefix' => $appPrefix,
    'folder' => $appFolder,
    'settings' => array('init' => $initConfig)
));

# check whether we use databases or not
if($initConfig->use_db == true)
{
    # retrieves the database application configs 
    $databases =  $configuration->parse($appFolder . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'databases',false);
    
    # check if there is a configuration defined 
    if(isset($databases) && count($databases) > 0)
    {
        $settings = Arrays::getElement($application->getSettings(),'init');

        # load and initialize the Database class – does not connect
        $database = new Database(array(
            "availableDatabases" => $databases,
            "defaultDatabase" => $settings->default_db
        ));
        
        # store the database object in the registry
        Packager::set("database",$database->initialize());
    }
    else
    { 
        throw new DatabaseException('No Databases Found !');
    }   
}

# check whether we use caching system or not
if($initConfig->use_cache == true)
{
    # retrieves the database application configs 
    $cacheConfig =  $configuration->parse($appFolder . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'cache');
    
    # check if there is a configuration defined 
    if(isset($cacheConfig))
    { 
        # load and initialize the Cache class – does not connect
        $cache = new Cache(array(
            "type" => $cacheConfig->cache->default->type,
            "options" => $cacheConfig->cache->default
        ));
        
        # store the cache object in the registry
        Packager::set("cache", $cache->initialize());
    }
    else
    {
        throw new DatabaseException('No configuration has been defined for the cache system');
    }  
}  

# stores the application object into the registry 
Packager::set('application',$application);