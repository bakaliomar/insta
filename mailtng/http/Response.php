<?php namespace ma\mailtng\http
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
    use ma\mailtng\www\URL as URL;
    use ma\mailtng\globals\Server as Server;
    use ma\mailtng\exceptions\types\BackendException as BackendException;
    /**
     * @name            Response.class 
     * @description     It's a class that deals with response methods
     * @package		ma\mailtng\http
     * @category        HTTP Class
     * @author		MailTng Team			
     */
    class Response
    {
        /** 
         * @readwrite
         * @access static 
         * @var integer
         */ 
        public static $messages = array(
            100 => 'Continue',
            101 => 'Switching Protocols',

            # Success 
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            # Redirection 
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found', 
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',

            # Client Error 
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',

            # Server Error 
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );

        /**
         * @name redirect
         * @description redirecting to a given url
         * @access static
         * @param string $url
         * @param integer $code
         * @param boolean $force
         * @return
         */
        public static function redirect($url, $code = 301, $force = true)
        {
            try
            {
                $url = ($url !='') ? $url : URL::getCurrentApplicationURL();
                header("Location: $url", $force, $code);
                exit;
            }
            catch (ErrorException $exception)
            {
                throw new BackendException($exception->getMessage(),$exception->getCode(),$exception);
            }
        }
        
        
        /**
         * @name redirect
         * @description redirecting to a given url
         * @access static
         * @param string $url
         * @param integer $code
         * @param boolean $force
         * @return
         */
        public static function redirectToPreviousPage()
        {
            try
            {
                $url = Server::get('HTTP_REFERER');
                \ma\mailtng\output\PrintWriter::printValue($url);
                header("Location: $url", true,301);
                exit;
            }
            catch (ErrorException $exception)
            {
                throw new BackendException($exception->getMessage(),$exception->getCode(),$exception);
            }
        }

        /**
         * @name header
         * @description set a header to the output page
         * @access static
         * @param integer $code
         * @param boolean $force
         * @param boolean $exit
         * @return
         * @throws BackendException
         */
        public static function header($code, $force = true, $exit = false)
        {
            try
            {
                if (!headers_sent())
                {
                    if (is_numeric($code))
                    {
                        header("HTTP/1.1 $code ".(self::$messages[$code]), true, $code);
                    }   
                    else
                    {
                        header($code, $force);
                    }    
                }

                if ($exit !== FALSE)
                {
                    die(self::$messages[$code]);
                }
            }
            catch (ErrorException $exception)
            {
                throw new BackendException($exception->getMessage(),$exception->getCode(),$exception);
            }
        }
    }
}