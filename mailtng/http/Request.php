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
    use ma\mailtng\globals\Server as Server;
    use ma\mailtng\types\Objects as Objects;
    /**
     * @name            Request.class 
     * @description     It's a class that deals with request methods
     * @package		ma\mailtng\http
     * @category        HTTP Class
     * @author		MailTng Team			
     */
    class Request
    {
        /** 
         * @readwrite
         * @access private | static 
         * @var array
         */ 
        private static $http_methods = array('GET', 'HEAD', 'OPTIONS', 'POST', 'PUT', 'DELETE');

        /**
         * @name __construct
         * @description class constructor
         * @notice we set it to private to prevent creating multiple instances
         * @access private
         * @return
         */
        private function __construct()
        {}
 
        /**
         * @name __clone
         * @description class cloning
         * @notice we set it to private to prevent cloning multiple instances
         * @access private
         * @return
         */
        private function __clone()
        {}
      
        /**
         * @name getParameter
         * @description get data by a key ( it checks if its a number then get the value from _parameters array else it checks according to the request method and gets either from _GET or _POST
         * @access static
         * @param string $key the key to get data with
         * @return mixed
         */
        public static function getParameter($key) 
        {
            if(strcmp(self::getMethod(),'GET') == 0)
            {
                if(isset($_GET[$key]))
                {
                    return $_GET[$key];
                }
                else
                {
                    return NULL;
                }
            }
            elseif(strcmp(self::getMethod(),'POST') == 0) 
            {
                if(isset($_POST[$key]))
                {
                    return $_POST[$key];
                }
                else
                {
                    return NULL;
                }            
            }
        }

        /**
         * @name getParameterFromPOST
         * @description get data by a key from $_POST
         * @access static
         * @param string $key the key to get data with
         * @param mixed $default the default data to get in case of empty results 
         * @return mixed
         */
        public static function getParameterFromPOST($key,$default = null) 
        {
            if(key_exists($key,$_POST))
            {
                return $_POST[$key];
            }
            return $default;           
        }

        /**
         * @name getParameterFromFILES
         * @description get data by a key from $_FILES
         * @access static
         * @param string $key the key to get data with
         * @param mixed $default the default data to get in case of empty results 
         * @return mixed
         */
        public static function getParameterFromFILES($key,$default = null) 
        {
            if(key_exists($key,$_FILES))
            {
                return $_FILES[$key];
            }
            return $default;       
        }

        /**
         * @name getParameterFromGET
         * @description get data by a key from $_GET
         * @access static
         * @param string $key the key to get data with
         * @param mixed $default the default data to get in case of empty results 
         * @return mixed
         */
        public static function getParameterFromGET($key,$default = null) 
        {
            if(key_exists($key,$_GET))
            {
                return $_GET[$key];
            }
            return $default;
        }

        /**
         * @name addToPOST
         * @description add data by a key in _POST 
         * @access static
         * @param string $key the key to get data with
         * @param string $value the value to store
         * @return
         */
        public static function addToPOST($key,$value) 
        {
            $_POST[$key] = $value;
        }

        /**
         * @name addToGET
         * @description add data by a key in _GET
         * @access static
         * @param string $key the key to get data with
         * @param string $value the value to store
         * @return
         */
        public static function addToGET($key,$value) 
        {
            $_GET[$key] = $value;
        }

        /**
         * @name getCookieData
         * @description get data by a key from $_COOCKIE
         * @access static
         * @param string $key the key to get data with
         * @return mixed
         */
        public static function getCookieData($key) 
        {
            if(isset($_COOKIE[$key]))
            {
                return $_COOKIE[$key];
            }
            else
            {
                return NULL;
            }
        }

        /**
         * @name getAllDataFromGET
         * @description get all data from _GET 
         * @access static
         * @return array
         */
        public static function getAllDataFromGET()     
        {
            return $_GET;
        }

        /**
         * @name getAllDataFromPOST
         * @description get all data from $_POST 
         * @access static
         * @return array
         */
        public static function getAllDataFromPOST()     
        {
            return $_POST;
        }

        /**
         * @name keyExistsInGET
         * @description check if $_GET[$key] is filled with some data
         * @access static
         * @param string $key the key to check with
         * @return boolean
         */
        public static function keyExistsInGET($key) 
        {
            return isset($_GET[$key]);
        }

        /**
         * @name keyExistsInPOST
         * @description check if $_POST[$key] is filled with some data
         * @access static
         * @param string $key the key to check with
         * @return boolean
         */
        public static function keyExistsInPOST($key) 
        {
            return isset($_POST[$key]);
        }

        /**
         * @name keyExistsInCOOCKIE
         * @description check if $_COOCKIE[$key] is filled with some data
         * @access static
         * @param string $key the key to check with
         * @return boolean
         */
        public static function keyExistsInCOOCKIE($key) 
        {
            return isset($_COOKIE[$key]);
        }

        /**
         * @name getRequestURL
         * @description get the current request url
         * @access static
         * @return string
         */
        public static function getRequestURL() 
        {
            return self::getParameterFromGET('url');
        }

        /**
         * @name getMethod
         * @description gets the request method either _GET or _POST or nothing in case of error 
         * @access static
         * @return string
         */
        public static function getMethod() 
        {
            $method = strtoupper(Server::get('REQUEST_METHOD'));
            if (!in_array($method, self::$http_methods)) 
            {
                throw new BackendException('Unknown request method');
            }
            return $method;
        }
        
        /**
         * @name sendPostRequest
         * @description send post requests
         * @access static
         * @return string
         */
        public static function sendPostRequest($url,$data) 
        {
            $response = null;

            # preparing the post data
            $post = array();

            $post = array_merge($post,$data);
            $postFields = http_build_query($post);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$postFields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        
        /**
         * @name getURLContents
         * @description gets URL content using sockets
         * @access static
         * @param string $request
         * @param string $method
         * @param integer $timeout
         * @return string
         */
        public static function getURLContents($request, $method = 'GET',$timeout = 10,$parameters = array()) 
        {
            $postData = '';
            $ch = curl_init($request);
            
            if ($ch) 
            {               
                curl_setopt($ch, CURLOPT_URL, $request);
                switch (strtoupper($method))
                {
                    case "GET":
                                curl_setopt($ch, CURLOPT_HTTPGET, true);
                                break;
                    case "POST":
                                curl_setopt($ch, CURLOPT_POST,1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS,  http_build_query($parameters)); 
                                echo $postData;
                                break;
                    default:
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                    break;
                }
                
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:11.0) Gecko/20100101 Firefox/11.0"); 
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($ch);
                
                $info = curl_getinfo($ch);
                
                if ($result === false || $info['http_code'] != 200) 
                {
                    $result = curl_error($ch);
                }
                
                curl_close($ch);
            }
           
            return trim($result);
        }

        /**
         * @name getURLHeaderLessContents 
         * @description gets URL content using sockets
         * @access static
         * @param string $request
         * @return string
         */
        public static function getURLHeaderLessContents($request) 
        {
            $content = "";
            
            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => "",
                CURLOPT_AUTOREFERER => true,
                CURLOPT_CONNECTTIMEOUT => 0,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_MAXREDIRS => 10
            );

            $ch = curl_init($request);
            curl_setopt_array($ch, $options);

            if (preg_match('`^https://`', $request)) 
            {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }

            $content = curl_exec($ch);
            curl_close($ch);
            return trim($content);
        }
        
        /**
         * @name download
         * @description downloads a file into a path
         * @access static
         * @param string $request
         * @param string $fileName
         * @return string
         */
        public static function download($request,$fileName) 
        {
            $result = 0;

            $fh = fopen($fileName, 'w');
            $ch = @\curl_init();
            \curl_setopt($ch, CURLOPT_URL, $request);
            \curl_setopt($ch, CURLOPT_FILE, $fh);
            \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $result = \curl_exec($ch);
            \curl_close($ch);
            fclose($fh);

            return $result;
        }
        
        
        /**
         * @name acceptLang
         * @description gets accept languages
         * @access static
         * @param string $lang
         * @return string
         */
        public static function acceptLang($lang = NULL) 
        {
            $accepts = self::_parseAccept($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (isset($lang)) 
            {
                return isset($accepts[$lang]) ? $accepts[$lang] : FALSE;
            }

            return $accepts;
        }

        /**
         * @name acceptType
         * @description gets accept type
         * @access static
         * @param string $type
         * @return string
         */
        public static function acceptType($type = NULL) 
        {
            $accepts = self::_parseAccept($_SERVER['HTTP_ACCEPT'], array('*/*' => 1.0));
            if (isset($type)) 
            {
                return isset($accepts[$type]) ? $accepts[$type] : $accepts['*/*'];
            }
            return $accepts;
        }

        /**
         * @name acceptEncoding
         * @description gets accept encoding
         * @access static
         * @param string $type
         * @return mixed
         */
        public static function acceptEncoding($type = NULL) 
        {
            $accepts = self::_parseAccept($_SERVER['HTTP_ACCEPT_ENCODING']);
            if (isset($type)) 
            {
                return isset($accepts[$type]) ? $accepts[$type] : FALSE;
            }
            return $accepts;
        }

        /**
         * @name ip2long
         * @description converts a string containing an (IPv4) Internet Protocol dotted address into a proper address
         * @access static
         * @param string $ipAddress
         * @return string
         */
        public static function ip2long ($ipAddress = null) 
        {
            if (empty ($ipAddress)) 
            {
                $ipAddress = Server::get('REMOTE_ADDR');
            }
            return sprintf('%u', ip2long($ipAddress));
        }

        /**
         * @name getBrowserLanguage
         * @description gets browser default language
         * @access static
         * @return string
         */
        public static function getBrowserLanguage()
        { 
            return substr(Server::get('HTTP_ACCEPT_LANGUAGE'), 0, 2);
        }  

        /**
         * @name getCallerIP
         * @description gets client IP 
         * @access static
         * @return string
         */
        public static function getClientIP()
        { 
            $ip = '';
            
            if (!empty($_SERVER['HTTP_CLIENT_IP']))
            {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } 
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } 
            else 
            {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            return $ip;
        }  
        
        /**
         * @name getCallerIP
         * @description gets client IP 
         * @access static
         * @return string
         */
        public static function getClientIPInformation()
        { 
            $result = array();
            $ip = self::getClientIP();
            
            if(filter_var($ip,FILTER_VALIDATE_IP))
            {
                $result = json_decode(file_get_contents("http://ipinfo.io/$ip/json"),true);
            }
            
            return $result;
        } 
        
        /**
         * @name _parseAccept
         * @description parses header accepts
         * @access protected | static
         * @param string $header
         * @param array $accepts
         * @return array
         */
        protected static function _parseAccept( &$header, array $accepts = NULL) 
        {
            if (!empty($header)) 
            {
                $types = explode(',', $header);

                foreach ($types as $type) 
                {
                    $parts = explode(';', $type);
                    $type = trim(array_shift($parts));
                    $quality = 1.0;

                    foreach ($parts as $part) 
                    {
                        if (strpos($part, '=') === FALSE) continue;

                        list ($key, $value) = explode('=', trim($part));

                        if ($key === 'q') 
                        {
                            $quality = (float) trim($value);
                        }
                    }
                    $accepts[$type] = $quality;
                }
            }

            $accepts = Objects::objectToArray($accepts);
            arsort($accepts);
            return $accepts;
        }
        
        /**
         * @name _sanitize
         * @description sanitizing values
         * @access protected | static
         * @param string $route
         * @param boolean $double
         * @return string
         */
        protected static function _sanitize($route, $double = false)
        {
            if ($double)
            {
                $route = preg_replace('#//+#', '/', $route);
            }
            $route = preg_replace('#\.[\s./]*/#', '', $route);
            return $route;
        }
    }
}