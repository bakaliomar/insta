<?php namespace ma\mailtng\debug
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
     * @name            Debug.class 
     * @description     It's a class that deals with debug methods like getting backtrace call , method call .....
     * @package		ma\mailtng\debug
     * @category        Helper Class
     * @author		MailTng Team			
     */
    class Debug extends Base
    {
        /**
         * @name getBackTrace
         * @description retrieves the whole debug backtrace
         * @access static
         * @param integer $level
         * @param array
         * @return
         */
        public static function getBackTrace($level = null) 
        {
            $debugBackTrace = debug_backtrace();
            if(isset($level) && is_numeric($level) && $level > 0)
            {
                if(isset($debugBackTrace) && is_array($debugBackTrace) && count($debugBackTrace) === $level && is_array($debugBackTrace[$level-1]))
                {
                    return $debugBackTrace[$level-1];
                }
            }
            return $debugBackTrace;
        }
    }
}