<?php namespace ma\mailtng\ssh2
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
     * @name            SSHAuthentication.class 
     * @description     It's a parent class of authentications types
     * @package		ma\mailtng\ssh2
     * @category        SSH
     * @author		MailTng Team			
     */
    class SSHAuthentication extends Base
    {}  
}