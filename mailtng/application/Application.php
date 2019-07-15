<?php namespace ma\mailtng\application
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
    use ma\mailtng\registry\Packager as Packager;
    use ma\mailtng\globals\Server as Server;
    use ma\mailtng\security\Security as Security;
    /**
     * @name            Application.class 
     * @description     The main application class
     * @package		ma\mailtng\application
     * @category        Core Class
     * @author		MailTng Team			
     */
    class Application extends Base
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_name;
       
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_folder;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_prefix;

        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_settings = array();

        /**
         * @name addSetting
         * @description stores a setting of the application
         * @access public
         * @param string $key
         * @param mixed $config
         * @return
         */
        public function addSetting($key,$config) 
        {
            $this->_settings[$key] = $config;
        }

        /**
         * @name getSetting
         * @description gets setting by a given key
         * @access public
         * @param string $key
         * @return mixed
         */
        public function getSetting($key) 
        {
            return isset($this->_settings[$key]) ? $this->_settings[$key] : NULL;
        }

        /**
         * @name getSettings
         * @description gets all settings
         * @access public
         * @return array
         */
        public function getSettings() 
        {
            return $this->_settings;
        }
       
        /**
         * @name getCurrent
         * @description gets the current application
         * @access static
         * @return Application
         */
        public static function getCurrent() 
        {
             return Packager::get('application');
        }

        /**
         * @name getPrefix
         * @description gets the prefix(and also folder name) of the current application
         * @access static
         * @return string
         */
        public static function getPrefix() 
        {
            $urlRaw = Server::get('REQUEST_URI');
            $urlRawParts = preg_split('/[\/?&=]+/', $urlRaw, -1, PREG_SPLIT_NO_EMPTY);
            
            # check if there is a default application defined
            if(defined('DEFAULT_APPLICATION_PREFIX') && DEFAULT_APPLICATION_PREFIX != '' && Security::applicationExists(DEFAULT_APPLICATION_PREFIX))
            {
                return DEFAULT_APPLICATION_PREFIX;
            }

            if(is_array($urlRawParts) && count($urlRawParts))
            {
                if(PROJECT_FOLDER != '')
                {
                    return $urlRawParts[1];
                }
                else
                {
                    return $urlRawParts[0];
                }
            }

            # in case of nothing found
            return '';
        }    
    }
}