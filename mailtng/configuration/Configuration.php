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
    use ma\mailtng\configuration\drivers\Ini as Ini;
    use ma\mailtng\configuration\drivers\Conf as Conf;
    use ma\mailtng\exceptions\types\ConfigurationException as ConfigurationException;
    /**
     * @name            Configuration.class 
     * @description     It deals with configuration files 
     * @package		ma\mailtng\configuration
     * @category        Configuration Parsing Class
     * @author		MailTng Team			
     */
    class Configuration extends Base
    {
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_type;

        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_options;        

        /**
         * @name initialize
         * @description Initializing the driver
         * @access public
         * @return mixed
         * @throws ConfigurationException
         */
        public function initialize() 
        {
            if (!$this->type) 
            {
                throw new ConfigurationException("Invalid type");
            }
            switch ($this->type) 
            {
                case "ini": 
                {
                    return new Ini($this->options);
                }
                case "conf": 
                {
                    return new Conf($this->options);
                }
                default: 
                {
                    throw new ConfigurationException("Invalid type");
                }
            }
        }
    }
}