<?php namespace ma\mailtng\security
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
    use ma\mailtng\www\URL as URL;
    use ma\mailtng\http\Response as Response;
    /**
     * @name            Security.class 
     * @description     It's a class that deals with security methods
     * @package		ma\mailtng\security
     * @category        Core Class
     * @author		MailTng Team			
     */
    class Security extends Base
    {
        /**
         * @name applicationExists
         * @description checks if an application exists
         * @access public
         * @param string $applicationPrefix
         * @return boolean
         */
        public static function applicationExists($applicationPrefix) 
        {
            if(isset($applicationPrefix) && $applicationPrefix != '' && is_dir(APPS_FOLDER. DS . $applicationPrefix . DS))
            {
                return true;
            }
            return false;
        } 
    }
}
