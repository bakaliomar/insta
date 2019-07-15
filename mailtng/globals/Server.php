<?php namespace ma\mailtng\globals
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
    use ma\mailtng\core\Base as Base;
    /**
     * @name            Server.class 
     * @description     It's a class that deals with the super global valriable $_SERVER manipulation methods
     * @package		ma\mailtng\globals
     * @category        Helper Class
     * @author		MailTng Team			
     */
    class Server extends Base
    {
        /**
         * @name get
         * @description gets a $_SERVER value in a secure way 
         * @access static
         * @param string $key
         * @return mixed
         */         
        public static function get($key) 
        {
           if(array_key_exists($key,$_SERVER))
           {
               if(!filter_var($_SERVER[$key], FILTER_SANITIZE_STRING))
               {
                   return NULL;
               }
               return $_SERVER[$key];
           }
           else
           {
               return NULL;
           }
        }

        /**
         * @name set
         * @description stores a $_SERVER value in a secure way 
         * @access static
         * @param string $key
         * @param string $value
         * @return
         */
        public static function set($key,$value) 
        {
            if (!filter_var($value, FILTER_SANITIZE_STRING)) 
            {
                $_SERVER[$key] = $value;
            }
        }
    }
}
