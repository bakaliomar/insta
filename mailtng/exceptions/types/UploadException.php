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
    /**
     * @name            UploadException.class 
     * @description     It's an exception class that deals with uploading errors
     * @package		ma\mailtng\exceptions\types
     * @category        Exception Class
     * @author		MailTng Team			
     */
    class UploadException extends BaseException
    {}    
}