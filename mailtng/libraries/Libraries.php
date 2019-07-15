<?php namespace ma\mailtng\libraries
{
    if (!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework       MailTng Framework
     * @version         1.1
     * @author          MailTng Team
     * @copyright       Copyright (c) 2015 - 2016.	
     * @license		
     * @link	
     */
    use ma\mailtng\files\Paths as Paths;
    /**
     * @name            Libraries.class 
     * @description     The main libraries loader class
     * @package		ma\mailtng\libraries
     * @category        Helper Class
     * @author		MailTng Team			
     */
    class Libraries
    {
        /**
         * @name loadLibrary
         * @description loads a library by it's name
         * @access static
         * @param string $libraryName
         * @return mixed
         */
        public static function loadLibrary($libraryName) 
        {
            if(isset($libraryName))
            {
                $apiPath = Paths::getCurrentApplicationRealPath() . DS . DEFAULT_LIBS_DIRECTORY . DS . strtolower($libraryName) . DS;
                
                if(is_dir($apiPath))
                {
                    require_once $apiPath . $libraryName . '.php';
                }   
            }
        }
    } 
}