<?php if(!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
/**
 * @framework       MailTng Framework
 * @version         1.1
 * @author          MailTng Team
 * @copyright       Copyright (c) 2015 - 2016.	
 * @license		
 * @link	
 * @name            init.conf.php 
 * @description     Init configuration file that contains our init constants and also loads the other config files ( autoload , log , error .... )
 * @package         .
 * @category        Config File
 * @author          MailTng Team			
 */

# Check if the php version installed is 5.3 or greater
version_compare(PHP_VERSION, '5.3', '<') and die('<pre> Requires PHP 5.3 or greater . </pre>');

# define the maximum execution time to 1 hour
ini_set('max_execution_time', 3600);

# define the socket timeout to 1 min
ini_set("default_socket_timeout", 60);

# define the maximum memory limit 
ini_set('memory_limit', '-1');

# disabling remote file include
ini_set("allow_url_fopen", 1);
ini_set("allow_url_include", 0);

# defining the default time zone
date_default_timezone_set("Etc/GMT");

# getting the peak of memory, in bytes, that's been allocated to our PHP script. 
define('START_MEMORY', memory_get_peak_usage(true));
define('START_TIME',microtime(true));

# defining separators
define('DS',DIRECTORY_SEPARATOR);
define('RDS','/');
define('ANS',"\\");

# framework information
define('FW_NAME','MailTng Framework');
define('FW_ABBR','mailtng');
define('FW_VERSION','1.1');
define('FW_AUTHOR','MailTng Team');
define('FW_FOLDER','mailtng');
define('FW_RELEASE_DATE','2013');
define('FW_DEFAULT_SKIN','default');
define('FW_VENDOR',"ma");

# project folder name ( if any )
define('PROJECT_FOLDER','');

# some default folder names
define('DEFAULT_APPS_DIRECTORY','applications');
define('DEFAULT_CONFIGS_DIRECTORY','configs');
define('DEFAULT_LOGS_DIRECTORY','logs');
define('DEFAULT_LIBS_DIRECTORY','libraries');
define('DEFAULT_SKINS_DIRECTORY','skins');
define('DEFAULT_CONTROLLERS_DIRECTORY','controllers');
define('DEFAULT_MODELS_DIRECTORY','models');
define('DEFAULT_VIEWS_DIRECTORY','views');
define('DEFAULT_HELPERS_DIRECTORY','helpers'); 
define('DEFAULT_PLUGINS_DIRECTORY','plugins');
define('DEFAULT_TEMPLATES_DIRECTORY','templates');
define('DEFAULT_LAYOUTS_DIRECTORY','layouts');
define('DEFAULT_TEMP_DIRECTORY','tmp');
define('DEFAULT_CACHE_DIRECTORY','cache');
define('DEFAULT_COOKIES_DIRECTORY','cockies');
define('DEFAULT_ASSETS_DIRECTORY','assets');

# defining some paths for the framework 
define('ROOT_PATH',dirname(__DIR__));
define('APPS_FOLDER',ROOT_PATH . DS . DEFAULT_APPS_DIRECTORY);

# default application case 
define('DEFAULT_APPLICATION_PREFIX','mailtng');

# defining default controller and action
define('DEFAULT_CONTROLLER','home');
define('DEFAULT_ACTION','index');

# defining coockies information
define("COOKIE_EXPIRE",time() + 60*60*24*30);
define("COOKIE_PATH",ROOT_PATH . DS . 'tmp' . DS . DEFAULT_COOKIES_DIRECTORY);

# define the environement ( dev for developpement phase and prod for production phase ) 
define('ENVIRONEMENT','dev');

# defining chmod values
define('FILE_READ_MODE',644);
define('FILE_WRITE_MODE',666);
define('DIR_READ_MODE',755);
define('DIR_WRITE_MODE',777);

# check for url rewriting

$_SERVER['REQUEST_URI'] = (filter_input(INPUT_SERVER, 'HTTP_X_REWRITE_URL') != null) ? filter_input(INPUT_SERVER, 'HTTP_X_REWRITE_URL') : filter_input(INPUT_SERVER, 'REQUEST_URI');

# including autoload config
require_once ROOT_PATH . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'autoload.conf.php';

# including log config
require_once ROOT_PATH .  DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'log.conf.php';

# including error config
require_once ROOT_PATH .  DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'error.conf.php';