<?php namespace ma\mailtng\exceptions\types
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
    use ma\mailtng\exceptions\BaseException as BaseException;
    use ma\mailtng\output\i18n\error\ErrorMessages as ErrorMessages;
    /**
     * @name            FatalException.class 
     * @description     It's an exception class that deals with fatal errors
     * @package		ma\mailtng\exceptions\types
     * @category        Exception Class
     * @author		MailTng Team			
     */
    class FatalException extends BaseException
    {}    
}