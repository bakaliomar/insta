<?php namespace ma\mailtng\output
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
    /**
     * @name            PrintWriter.class 
     * @description     It's a class that deals with output methods
     * @package		ma\mailtng\output
     * @category        Helper Class
     * @author		MailTng Team			
     */
    class PrintWriter extends Base
    {
        /**
         * @name printValue
         * @description prints Values in the screen
         * @access static
         * @param mixed $input
         * @param boolean $exit
         * @param string $parentWrapper
         * @param string $style
         * @return
         */  
        public static function printValue($input,$exit = true,$parentWrapper = 'pre',$style = '')
        {
            # forcing the controller to display an error page instead of the requested one
            $controller = Packager::get("controller");

            if(isset($controller))
            {
                $controller->setShowMasterView(false);
                $controller->setShowPageView(false);
            }
            
            echo $parentWrapper != null && $parentWrapper != '' ? '<'.$parentWrapper.' style="'.$style.'" >' : '';
            print_r($input);
            echo $parentWrapper != null && $parentWrapper != '' ? '</'.$parentWrapper.'>' : '';
            if($exit) exit;
        }   
    }
}