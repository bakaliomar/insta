<?php namespace ma\mailtng\registry
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
    /**
     * @name            Packager.class 
     * @description     It's a class created based on both registry and singleton design patterns it plays the role of a packager ( a registry of variables - objects , arrays , text ...... - to prevent initializing them again 
     * @package		ma\mailtng\registry
     * @category        Word Wide Web Class
     * @author		MailTng Team			
     */
    class Packager
    {       
        /** 
         * @read
         * @access private 
         * @var array
         */ 
        private static $_instances = array();  
        
        /**
         * @name __construct
         * @description private constructor to prevent it being created directly
         * @access private
         * @return
         */ 
        private function __construct()  
        {}  
  
        /**
         * @name __clone
         * @description private clone to prevent it being cloned directly
         * @access private
         * @return
         */ 
        private function __clone()  
        {}  
 
        /**
         * @name set
         * @description stores an instance ( could be anything , objects , settings , arrays .... ) inside our packager
         * @access static
         * @param string $key  
         * @param string $instance 
         * @return
         */
        public static function set($key,$instance)  
        {  
            self::$_instances[$key] = $instance;  
        }  
 
        /**
         * @name get
         * @description gets an instance from our packager 
         * @access static
         * @param string $key
         * @param string $default  
         * @return mixed
         */
        public static function get($key,$default = null)  
        {  
            if(array_key_exists($key,self::$_instances))  
            {  
                return self::$_instances[$key];  
            }  
            return $default;
        } 
 
        /**
         * @name erase
         * @description erases an instance from our packager 
         * @access static
         * @param string $key  
         * @return
         */
        public static function erase($key)  
        {   
            unset(self::$_instances[$key]);  
        }
        
        /**
         * @name getAllInstances
         * @description returns all instances
         * @access static  
         * @return array
         */
        public static function getAllInstances() 
        {
            return self::$_instances;
        }
    }  
}