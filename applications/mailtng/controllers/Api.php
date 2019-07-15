<?php namespace ma\applications\mailtng\controllers
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
    use ma\mailtng\application\Controller as Controller;
    use ma\mailtng\database\Database as Database;
    use ma\mailtng\http\Request as Request;
    use ma\mailtng\files\Paths as Paths;
    use ma\mailtng\os\System as System;
    /**
     * @name            Api.controller 
     * @description     The Api controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Api extends Controller 
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_secretKey = 'x0ja8s4a3duqk9e2w6vga91hrvi7t14wrdxpv754aql055tr2ee2d59b6hop';

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_httpVersion = "HTTP/1.1";
        
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_supportedTypes = array('application/json','application/xml','text/html');
        
        /**
         * @name index
         * @description the index action
         */
        public function index() 
        {
            $this->setShowMasterView(false);
            $this->setShowPageView(false);
            
            $apiKey = Request::getParameterFromPOST('api_key');
            $contentType = in_array(Request::getParameterFromPOST('result_type',''),$this->_supportedTypes) ? Request::getParameterFromPOST('result_type','') : 'application/json';
            $functionName = Request::getParameterFromPOST('func_name');
            
            if($apiKey != null && $apiKey == $this->_secretKey)
            {
                switch ($functionName) 
                {
                    case 'track_actions' :
                    {
                        $script = Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'tracking' . DS . 'actions.php';

                        $agent = Request::getParameterFromPOST('agent');
                        $ip = Request::getParameterFromPOST('ip');
                        $language = Request::getParameterFromPOST('language','EN');
                        $data = Request::getParameterFromPOST('data');
                        $message = Request::getParameterFromPOST('message');
    
                        # parameters validation
                        if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4))
                        {
                            $this->displayResponse(500,$contentType,array(),'invalid IP!');
                        }
                        elseif(empty($agent))
                        {
                            $this->displayResponse(500,$contentType,array(),'user_agent is empty!');
                        }
                        elseif(empty($data))
                        {
                            $this->displayResponse(500,$contentType,array(),'data is empty!');
                        }
                        else
                        {
                             # execute the script
                            $result['status'] = 500;
                            $result['message'] = System::executeCommand("php $script $agent $ip $language $data $message",true);
                            
                            if($result['status'] == 500)
                            {
                                $this->displayResponse($result['status'],$contentType,array(),$result['message']);
                            }
                            else
                            {
                                $this->displayResponse($result['status'],$contentType,$result['message']);
                            }
                        }
                        break;
                    }
                    case 'complete_drop' :
                    {
                        $dropId = intval(Request::getParameterFromPOST('drop_id'));
                        $status = Request::getParameterFromPOST('status');
                        $progress = intval(Request::getParameterFromPOST('progress'));
                        $finishTime = Request::getParameterFromPOST('finish_time');

                        # parameters validation
                        if($dropId == 0)
                        {
                            $this->displayResponse(500,$contentType,array(),'invalid drop id!');
                        }
                        else
                        {
                            # connect to the default database 
                            Database::secureConnect();
                            
                            # build the query
                            $query = "UPDATE production.drops SET status = '$status' , finish_time = '$finishTime' , sent_progress = '$progress' WHERE id = $dropId";

                            # execute the query 
                            $result = Database::getCurrentDatabaseConnector()->executeQuery($query,true);

                            
                            if($result != null)
                            {
                                $this->displayResponse(200,$contentType,'drop updated succesfully!');
                            }
                            else
                            {
                                $this->displayResponse(500,$contentType,array(),'error while updating drop!');
                            }
                            
                            # disconnect from all databases 
                            Database::secureDisconnect();
                        }
                        break;
                    }
                    case 'calculate_drop' :
                    {
                        $data = @unserialize(Request::getParameterFromPOST('data'));

                        # parameters validation
                        if(count($data) == 0)
                        {
                            $this->displayResponse(500,$contentType,array(),'no data found!');
                        }
                        else
                        {
                            # connect to the default database 
                            Database::secureConnect();
                            
                            $result = null;

                            foreach ($data as $row) 
                            {
                                $dropId = intval($row['drop_id']);
                                $ipId = intval($row['ip_id']);
                                $delivered = intval($row['delivered']);
                                $bounced = intval($row['bounced']);
                                
                                # build the query
                                $query = "UPDATE production.drop_ips set delivered = '$delivered' , bounced = '$bounced' WHERE drop_id = $dropId AND ip_id = $ipId";

                                # execute the query 
                                $result = Database::getCurrentDatabaseConnector()->executeQuery($query,true);
                            }
                            
                            if($result != null)
                            {
                                $this->displayResponse(200,$contentType,'drop ips updated succesfully!');
                            }
                            else
                            {
                                $this->displayResponse(500,$contentType,array(),'error while updating drop ips!');
                            }
                            
                            # disconnect from all databases 
                            Database::secureDisconnect();
                        }
                        break;
                    }
                    default:
                    {
                        $this->displayResponse(500,$contentType,array(),'function not found!');
                        break;
                    }
                }
            }
            else
            {
                $this->displayResponse(500,$contentType,array(),'incorrect api key!');
            }
        }

        /**
         * @name getHttpStatusMessage
         * @description gets status message
         * @once
         * @protected
         */
        public function getHttpStatusMessage($statusCode)
        {
            $httpStatus = array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                306 => '(Unused)',
                307 => 'Temporary Redirect',
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
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported');
            return ($httpStatus[$statusCode]) ? $httpStatus[$statusCode] : $httpStatus[500];
        }
        
        /**
         * @name displayResponse
         * @description displays response
         * @once
         * @protected
         */
        public function displayResponse($statusCode,$contentType,$result,$errorMessage = null,$die = false)
        {
            $statusMessage = $this->getHttpStatusMessage($statusCode);
            header("{$this->_httpVersion} $statusCode $statusMessage");		
            $this->setDefaultContentType($contentType);
            
            $response = array();
            
            $response['contentType'] = $contentType;
            $response['statusCode'] = $statusCode;
            $response['statusMessage'] = $statusMessage;
            
            if(!empty($errorMessage))
            {
                $response['errorMessage'] = $errorMessage;
            }
            else
            {
                $response['results'] = $result;
            }

            if($die == true)
            {
                die(json_encode($response,JSON_FORCE_OBJECT));
            }
            else
            {
                echo json_encode($response,JSON_FORCE_OBJECT);
            }
        }
        
        /**
         * @name getScriptResult
         * @description gets scripts result
         * @once
         * @protected
         */
        public function getScriptResult($result)
        {
            return (count($result) && key_exists('output',$result) && count($result['output'])) ? json_decode($result['output'][0],true) : array('status' => 500,'message' => 'Internal Server Error !');
        }
    } 
}