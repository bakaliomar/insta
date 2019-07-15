<?php if(!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
/**
 * @framework       MailTng Framework
 * @version         1.1
 * @author          MailTng Team
 * @copyright       Copyright (c) 2015 - 2016.	
 * @license		
 * @link	
 */
use ma\mailtng\debug\Debug as Debug;
use ma\mailtng\exceptions\types\ClassNotFoundException as ClassNotFoundException;
/**
 * @name            autoload.conf.php 
 * @description     Autoload config that stores the paths for any instanciation of a class to prevent including the class file everytime
 * @package         .
 * @category        Config File
 * @author          MailTng Team			
 */

# Registering autoload function
spl_autoload_register('loadClass');

/**
 * @name loadClass
 * @description plays the role of autoloader of classes
 * @param string $class the class name
 * @protected
 * @throws ClassNotFoundException
 */
function loadClass($class)
{
    $fileName = str_replace('_', DS, str_replace(ANS, DS, ltrim(str_replace(FW_VENDOR . ANS,"",$class), ANS))) . '.php';

    if(file_exists(ROOT_PATH . DS . $fileName))
    {
        require ROOT_PATH . DS . $fileName;
    }
    else
    {
        $debug = Debug::getBackTrace(3);
        $file = $debug && count($debug) && key_exists("file",$debug) ? $debug['file'] : null;
        $line = $debug && count($debug) && key_exists("line",$debug) ? $debug['line'] : null;
        throw new ClassNotFoundException("{$class} class does not exist !",500,null,$file,$line);
    }
}

