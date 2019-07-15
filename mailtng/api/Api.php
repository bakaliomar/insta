<?php namespace ma\mailtng\api
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
    use ma\mailtng\api\Cake as Cake;
    use ma\mailtng\api\Hitpath as Hitpath;
    /**
     * @name            Api.class 
     * @description     It's a class that deals with API methods
     * @package		ma\mailtng\api
     * @category        API
     * @author		MailTng Team			
     */
    class Api
    {
        public static function getAPIClass($sponsor)
        {
            $api = null;
            
            if(count($sponsor))
            {
                switch ($sponsor['api_type']) 
                {
                    case 'cake':
                    {
                        $api = new Cake(array(
                            "url" => $sponsor['api_url'],
                            "email" => $sponsor['username'],
                            "password" => $sponsor['password'],
                            "affiliateId" => $sponsor['affiliate_id'],
                            "key" => $sponsor['api_key']
                        ));
                        break;
                    }
                    case 'hitpath':
                    {
                        $api = new Hitpath(array(
                            "url" => $sponsor['api_url'],
                            "username" => $sponsor['username'],
                            "password" => $sponsor['password'],
                            "affiliateId" => $sponsor['affiliate_id'],
                            "key" => $sponsor['api_key']
                        ));
                        break;
                    }
                }
            }
        
            return $api;
        }
    }
}



