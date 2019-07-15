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
    use ma\mailtng\application\Application as Application;
    use ma\mailtng\http\Request as Request;
    use ma\mailtng\http\Response as Response;
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\www\URL as URL;  
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\globals\Server as GloblServers;
    use ma\applications\mailtng\models\admin\Server as Server;
    use ma\applications\mailtng\models\admin\ServerProvider as ServerProvider;
    use ma\mailtng\os\System as System;
    use ma\mailtng\files\Paths as Paths;
    use ma\mailtng\mail\MailBoxManager as MailBoxManager;
    use ma\mailtng\ssh2\SSH as SSH;
    use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
    use ma\mailtng\pmta\PmtaHeader as PmtaHeader;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\exceptions\types\PageException as PageException; 
    
    /**
     * @name            Tools.controller 
     * @description     The Tools controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Tools extends Controller 
    {
        /**
         * @name init
         * @description initializing proccess before the action method executed
         * @once
         * @protected
         */
        public function init() 
        {
            # connect to the default database 
            Database::secureConnect();

            # check authentication
            $user = Session::get('mailtng_connected_user');  
            
            if(!isset($user))
            {
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'authentication' . RDS . 'login.html');
            }
            
            # check authorization access
            if(!in_array(Arrays::getElement($user,'application_role_id'),array(1)))
            {
                throw new PageException("403 Access Denied",403);
            }
        }

        /**
         * @name index
         * @description the index action
         * @before init
         * @after setMenu,closeConnection
         */
        public function index() 
        {}
        
        /**
         * @name index
         * @description the negative action
         * @before init
         * @after closeConnection
         */
        public function negative() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_tools_negative',true);
            
            $arguments = func_get_args(); 
            $formType = isset($arguments) && count($arguments) ? $arguments[0] : null;

            if(isset($formType))
            {
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                $formData = Request::getAllDataFromPOST();
                
                switch ($formType) 
                {
                    case 'retrieve' :
                    {
                        $email = Arrays::getElement($formData,'email');
                        $password = Arrays::getElement($formData,'password');
                        $words = '';
                        $headerCondition = 'D02';
                        $regex = "#<style>(.*?)</style>#";
                        
                        if (isset($email) && isset($password) && filter_var($email,FILTER_VALIDATE_EMAIL))
                        {
                            $isp = Arrays::getElement(explode('.',Arrays::getElement(explode('@',$email),1)),0);

                            $mailbox = new MailBoxManager();
                            $mailbox->setIsp(trim($isp));

                            # inbox case
                            $mailbox->setFolder('inbox');
                            $mailbox->connect($email, $password);
                            $mailbox->sortEmails();
                            $emailsIds = $mailbox->getEmailsIds();

                            foreach ($emailsIds as $id) 
                            {
                               $header = new PmtaHeader();
                               $header->convertHeaderTextToParameters($mailbox->getEmailHeader($id));
                               $body = $mailbox->getEmail($id);
                               
                               # get html content
                               $html = key_exists('body-html',$body) ? trim($body['body-html']) : '';
                               $headerStatus = trim($header->getHeaderParameter('CMM-X-Message-Delivery'));

                               if(strpos($html,'charset=utf-8"><style>') > -1 && Strings::endsWith($headerStatus,$headerCondition) == TRUE)
                               {
                                   $matches = array();
                                   preg_match($regex,$html, $matches);
                                   
                                   if(count($matches) == 2 && strlen($matches[1]) > 0)
                                   {
                                       $words .= trim($matches[1]) . PHP_EOL;
                                   }
                               }
                            }

                            # disconnect from inbox 
                            $mailbox->disconnect();
                            
                            die(json_encode(array("words" => rtrim($words,PHP_EOL))));
                        }
                        else
                        {
                            die(json_encode(array("type" => "error" , "message" => "Please check your credentials !")));
                        }
                        
                        break;
                    }
                    case 'send' :
                    {
                        $serversId = intval(Arrays::getElement($formData,"server-id"));
                        $ipId = intval(Arrays::getElement($formData,"ip-id"));
                        $header = Arrays::getElement($formData,"header");
                        $words = Arrays::getElement($formData,"words");
                        $rcpt = Arrays::getElement($formData,"rcpt");
                        
                        # validations
                        if ($serversId == 0)
                        {
                            die(json_encode(array("type" => "error" , "message" => "Please select a server !")));
                        }
                        
                        if ($ipId == 0)
                        {
                            die(json_encode(array("type" => "error" , "message" => "Please select an ip !")));
                        }
                        
                        if (empty(trim(trim($header),PHP_EOL)))
                        {
                            die(json_encode(array("type" => "error" , "message" => "Please specify the header to send !")));
                        }
                        
                        if (empty(trim(trim($words),PHP_EOL)))
                        {
                            die(json_encode(array("type" => "error" , "message" => "Please specify the words to send !")));
                        }
                        
                        if (!filter_var($rcpt,FILTER_VALIDATE_EMAIL))
                        {
                            die(json_encode(array("type" => "error" , "message" => "Please check your RCPT !")));
                        }
                        
                        # write the form into a file
                        $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'negs';
                        $fileName = 'neg_' . Strings::generateRandomText(5,true,true,true,false) . '.dpl';

                        # convert the form data into json to store it into a file so that the mailing script will read it 
                        $jsonVersionOfDrop = json_encode($formData);

                        if($jsonVersionOfDrop != '' && file_put_contents($fileDirectory . DS . $fileName, $jsonVersionOfDrop)) 
                        {  
                            # start the send 
                            System::executeCommand('nohup php ' . Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'negative_proccess' . DS . 'negative_send.php ' . $fileName . ' &');
                        }
                        
                        die(json_encode(array("type" => "success" , "message" => "Negative send has been procceed Successfully !")));
                        break;
                    }
                }
            }
            
            # get all servers from the database
            $serverProviders = ServerProvider::all(true,array('status_id = ? ',1),array('id','name'));
            $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','provider_id'));

            # check for providers that has no servers in production 
            foreach ($serverProviders as $index => $provider) 
            {
                $count = 0;
                
                foreach ($servers as $server) 
                {
                    if($server['provider_id'] == $provider['id'])
                    {
                        $count++;
                    }
                }
                
                if($count == 0)
                {
                    unset($serverProviders[$index]);
                }
            }
            
            # get the header template 
            $header = '';
            $file = APPS_FOLDER . DS . Application::getPrefix() . DS . DEFAULT_ASSETS_DIRECTORY . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'header.tpl';

            if(file_exists($file))
            {
                $header = file_get_contents($file);
            }
            
            $this->getPageView()->set('servers',$servers);
            $this->getPageView()->set('serverProviders',$serverProviders);
            $this->getPageView()->set('header',$header);
        }
        
        /**
         * @name tzones
         * @description the tzones action
         * @before init
         * @after closeConnection
         */
        public function tzones() 
        { 
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_tools_zone',true);
            
            # retrieve all form data
            $formData = Request::getAllDataFromPOST();
            
            # execution case
            if(isset($formData) && count($formData))
            {
                # get all production servers
                $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','main_ip','username','password','ssh_port'));
                $time = str_replace("GMT","",trim(Arrays::first(Arrays::getElement(System::executeCommand("date",true),'output'))));
                $timezone = Request::getParameterFromPOST("time-zone","GMT");
                
                if(count($servers))
                {
                    foreach ($servers as $server) 
                    {
                        $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                        $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                        if($sshConnector->isConnected())
                        {
                            $sshConnector->cmd("rm -rf  /etc/localtime;");
                            $sshConnector->cmd("yes | cp -rf /usr/share/zoneinfo/$timezone /etc/localtime;");
                            $sshConnector->cmd("date -s \"{$time}\";");
                            $sshConnector->disconnect();
                        } 
                    }
                }
                
                $message = "TimeZone Updated successfully !";
                $messageFlag = 'success';
                
                # stores the message in the session 
                Session::set('proccess_message_flag',$messageFlag);
                Session::set('proccess_message',$message);
               
                # check for message 
                PagesHelper::checkForMessageToPage($this);
            }
        }

        /**
         * @name getIps
         * @description gets the ips by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getIps() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $serverId = isset($arguments) && count($arguments) ? intval($arguments[0]) : null;

                if(isset($serverId) && !empty($serverId) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the ips from the database
                    $ips = Database::getCurrentDatabaseConnector()->executeQuery("SELECT i.id AS id,"
                                            . " i.value AS value,"
                                            . " i.rdns AS rdns,"
                                            . " s.name AS server,"
                                            . " s.id AS serverid"
                                            . " FROM admin.ips i"
                                            . " LEFT JOIN admin.servers s ON s.id = i.server_id"
                                            . " WHERE i.status_id = 1 AND i.server_id = $serverId"
                                            . " ORDER BY s.id ASC", true);
                    
                    die(json_encode(array( "ips" => $ips)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name closeConnection
         * @description close any open connections
         * @protected
         */
        public function closeConnection() 
        {
            # disconnect from all databases 
            Database::secureDisconnect();
        }  
    } 
}