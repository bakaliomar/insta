<?php namespace ma\mailtng\configuration
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
     * @name            Driver.class 
     * @description     The main driver class that all upcoming drivers have to extends from it.
     * @package		ma\mailtng\configuration
     * @category        Configuration Parsing Class
     * @author		MailTng Team			
     */
    class Driver extends Base
    {
        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_parsed = array();
        
        /**
         * @name initialize
         * @description Initializing the driver
         * @access public
         * @return mixed
         */
        public function initialize()
        {
            return $this;
        }  
    }
}