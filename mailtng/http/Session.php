<?php namespace ma\mailtng\http
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
     * @name            Session.class 
     * @description     It's a class that deals with session's methods
     * @package		ma\mailtng\http
     * @category        HTTP Class
     * @author		MailTng Team			
     */
    class Session
    {
        /**
         * @name set
         * @description set variables to $_SESSION array
         * @access static
         * @param string $property the property to set to the session
         * @param mixed $value the balue to set to the property
         * @param boolean $makeItArray make the value as a new array inside the session
         * @return
         */
        public static function set($property, $value = null, $makeItArray = false) 
        {
            self::start();

            if (is_array($property)) 
            {
                foreach ($property as $key => $singleProperty) 
                {
                    $_SESSION[$key] = $singleProperty;
                }
            } 
            else 
            {
                if ($makeItArray == false) 
                {
                    $_SESSION[$property] = $value;
                } 
                else 
                {
                    $_SESSION[$property][] = $value;
                }
            }
            
            self::releaseLock();
        }

        /**
         * @name del
         * @description deletes variables from $_SESSION array
         * @access static
         * @param string $property the property to delete from the session
         * @return
         */
        public static function del($property) 
        {
            self::start();
            
            if (is_array($property)) 
            {
                foreach ($property as $key => $singleProperty) 
                {
                    $singleProperty = null;
                    unset($_SESSION[$key]);
                }
            } 
            else 
            {
                $_SESSION[$property] = null;
                unset($_SESSION[$property]);
            }
            
            self::releaseLock();
        }

        /**
         * @name get
         * @description get variables from $_SESSION array.
         * @access static
         * @param string $property the key of the value to get from the session
         * @param mixed $default the default value retrieved incase of no result found
         * @return mixed
         */
        public static function get($property,$default = null) 
        {
            if (key_exists($property,$_SESSION)) 
            {
                return $_SESSION[$property];
            } 
            return $default;
        }

        /**
         * @name getThenDel
         * @description get variables from $_SESSION array then deletes it from the session.
         * @access static
         * @param string $property the key of the value to get from the session
         * @return mixed
         */
        public static function getThenDel($property) 
        {
            $result = self::get($property);
            self::del($property);
            return $result;
        }

        /**
         * @name start
         * @description start session
         * @access static
         * @return
         */
        public static function start() 
        {
            session_start();
        }
        
        /**
         * @name destroy
         * @description destroys the session and empty data array
         * @access static
         * @return
         */
        public static function destroy() 
        {
            self::start();
            session_destroy();
            unset($_SESSION);
            $_SESSION = array();
            self::releaseLock();
        }
        
        /**
         * @name release
         * @description releases the session
         * @access static
         * @return
         */
        public static function releaseLock() 
        {
            session_write_close();
        }
    }
}