<?php namespace ma\mailtng\configuration\drivers
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
    use ma\mailtng\caching\Driver as Driver;
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\exceptions\types\ParseException as ParseException;
    use ma\mailtng\exceptions\types\ConfigurationException as ConfigurationException;
    /**
     * @name            Ini.class 
     * @description     The .ini configuration files parsing driver 
     * @package		ma\mailtng\configuration\drivers
     * @category        Configuration Parsing Class
     * @author		MailTng Team			
     */
    class Ini extends Driver
    {   
        /**
         * @name parse
         * @description parses an .ini file
         * @access public
         * @param string $path
         * @param boolean $objectFormat
         * @return mixed
         * @throws ConfigurationException
         */
        public function parse($path,$objectFormat = true) 
        {
            if (empty($path)) 
            {
                throw new ConfigurationException("\$path argument is not valid");
            }
            
            if (!file_exists("{$path}.ini")) 
            {
                throw new ConfigurationException("The file that you provided was not found");
            }
            
            if (!isset($this->_parsed[$path])) 
            {
                $config = array();
                
                ob_start();
                include("{$path}.ini");
                $string = ob_get_contents();
                ob_end_clean();
                
                $pairs = parse_ini_string($string);
                
                if ($pairs == false) 
                {
                    throw new ParseException("Could not parse configuration file");
                }
                
                foreach ($pairs as $key => $value) 
                {
                    $config = $this->_pair($config, $key, $value);
                }
                
                $this->_parsed[$path] = ($objectFormat) ? Arrays::arrayToObject($config) : $config;
            }
            
            return $this->_parsed[$path];
        }
        
        /**
         * @name _pair
         * @description pairs a configuration input
         * @access protected
         * @param array $config
         * @param string $key
         * @param string $value
         * @return array
         */
        protected function _pair($config, $key, $value) 
        {
            if (strstr($key, ".")) 
            {
                $parts = explode(".", $key, 2);
                if (empty($config[$parts[0]])) 
                {
                    $config[$parts[0]] = array();
                }
                $config[$parts[0]] = $this->_pair($config[$parts[0]], $parts[1], $value);
            } 
            else 
            {
                $config[$key] = $value;
            }
            return $config;
        }
    }
}