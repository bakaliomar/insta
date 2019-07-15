<?php namespace ma\applications\mailtng\helpers
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
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\http\Session as Session;
    /**
     * @name            PagesHelper.class 
     * @description     The PagesHelper class
     * @package		ma\applications\mailtng\helpers
     * @category        Helper
     * @author		MailTng Team			
     */
    class PagesHelper
    {
        /**
         * @name buildStatsTableForExcel
         * @description build stats table for excel
         * @access static
         * @param mixed $controller
         * @return
         */
        public static function checkForMessageToPage($controller)
        {
            if($controller)
            {
                # check if there is a message from a previous action
                $message = Session::getThenDel('proccess_message');

                if(isset($message))
                {
                    $messageFlag = Session::getThenDel('proccess_message_flag');
                    $button = $messageFlag == 'error' ? 'btn-danger' : 'btn-primary';

                    $html = '<script>MailTng.alertBox({title:"' . $message . '",type:"' . $messageFlag . '",allowOutsideClick:"true",confirmButtonClass:"' . $button . '"});</script>';

                    # set the message into the template data system 
                    $controller->getMasterView()->set('prev_action_message',$html);
                }
            }
        }
    }
}