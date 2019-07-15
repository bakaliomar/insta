<?php namespace ma\mailtng\routing\routes
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
    use ma\mailtng\routing\Route as Route;
    /**
     * @name            Regex.class 
     * @description     It has a match method that all other router classes will work with to creates the correct regular expression search string and returns any matches to the provided URL
     * @package		ma\mailtng\routing\routes
     * @category        Routing Class
     * @author		MailTng Team			
     */
    class Regex extends Route 
    {
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_keys;

        /**
         * @name matches
         * @description creates the correct regular expression search string and returns any matches to the provided URL
         * @access public
         * @param string $url  
         * @return boolean
         */
        public function matches($url) 
        {
            $pattern = $this->pattern;
            $values = array();
                    
            # check values
            preg_match_all("#^{$pattern}$#", $url, $values);

            if (sizeof($values) && sizeof($values[0]) && sizeof($values[1])) 
            {
                # values found, modify parameters and return
                $derived = array_combine($this->keys, $values[1]);
                $this->parameters = array_merge($this->parameters, $derived);

                return true;
            }
            return false;
        }
    }
}