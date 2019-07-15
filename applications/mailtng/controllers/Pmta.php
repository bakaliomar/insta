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
    use ma\mailtng\files\Paths as Paths;
    use ma\mailtng\configuration\Configuration as Configuration;
    use ma\mailtng\database\Database as Database;
    use ma\mailtng\http\Request as Request;
    use ma\mailtng\http\Response as Response;
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\www\URL as URL;   
    use ma\mailtng\ssh2\SSH as SSH;
    use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
    use ma\mailtng\types\Arrays as Arrays;
    use ma\applications\mailtng\models\admin\Server as Server;
    use ma\applications\mailtng\models\admin\ServerProvider as ServerProvider;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\exceptions\types\PageException as PageException;
    /**
     * @name            Pmta.controller 
     * @description     The Pmta controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Pmta extends Controller 
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
            if(!in_array(Arrays::getElement($user,'application_role_id'),array(1,2)))
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
         * @name clear
         * @description the clear action
         * @before init
         * @after setMenu,closeConnection
         */
        public function clear() 
        {
            $productionServers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','main_ip','username','password','ssh_port'));
            
            foreach ($productionServers as $server) 
            {
                $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);
                            
                if($sshConnector->isConnected())
                {
                    $sshConnector->cmd("rm -rf /var/log/pmta/pmta.log.*; rm -rf /var/log/pmta/pmtahttp.log.*; > pmta.log ; > pmtahttp.log;",true);
                    $sshConnector->disconnect();
                }  
            }
        }
        
        /**
         * @name manage
         * @description the manage action
         * @before init
         * @after setMenu,closeConnection
         */
        public function manage() 
        {           
            # retrieve all form data
            $data = Request::getAllDataFromPOST();
            $results = "";
            
            if(count($data))
            {
                $this->setShowMasterView(false);
                
                # execute pmta commands
                $serversIds = Arrays::getElement($data,"servers",array());
                $action = Arrays::getElement($data,"action",'');
                
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                if(count($serversIds) == 0)
                {
                    $message = "Please Select At Least One Server !";
                }
                else
                {
                    switch ($action) 
                    {
                        case 'stop-pmta':
                        {
                            $command = "service pmta stop;";
                            break;
                        }
                        case 'restart-pmta':
                        {
                            $command = "service pmta restart;pmta reset counters;";
                            break;
                        }
                        case 'reload-pmta':
                        {
                            $command = "service pmta reload;";
                            break;
                        }
                        case 'clean-queue':
                        {
                            $queues = Arrays::getElement($data,"queue-parameter",'*/*');
                            $queues = trim($queues) == '' ? '*/*' : $queues;
                            $command = "pmta delete '--queue=$queues';";
                            break;
                        }
                        case 'pause-queue':
                        {
                            $queues = Arrays::getElement($data,"queue-parameter",'*/*');
                            $queues = trim($queues) == '' ? '*/*' : $queues;
                            $command = "pmta pause queue '$queues';";
                            break;
                        }
                        case 'resume-queue':
                        {
                            $queues = Arrays::getElement($data,"queue-parameter",'*/*');
                            $queues = trim($queues) == '' ? '*/*' : $queues;
                            $command = "pmta resume queue '$queues';";
                            break;
                        }
                        case 'schedule-queue':
                        {
                            $queues = Arrays::getElement($data,"schedule-parameter",'*/*');
                            $queues = trim($queues) == '' ? '*/*' : $queues;
                            $times = intval(Arrays::getElement($data,"schedule-times",'1'));
                            $command = "";
                            
                            for ($index1 = 0; $index1 < $times; $index1++) 
                            {
                                $command .= "pmta schedule '$queues';";
                            }
                            
                            break;
                        }
                        case 'delete-job':
                        {
                            $jobId = intval(Arrays::getElement($data,"job-id",'0'));
                            $command = "pmta delete '--jobId=mailer_$jobId';";
                            break;
                        }
                        case 'show-jobs':
                        {
                            $command = "pmta show jobs;";
                            break;
                        }
                    }

                    $productionServers = Server::all(true,array('server_type_id = ? AND status_id = ? AND id IN (' . implode(",", $serversIds) . ') ',array(2,1)),array('id','name','main_ip','username','password','ssh_port'));
                    
                    $index = 0;

                    foreach ($productionServers as $server) 
                    {
                        $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                        $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                        if($sshConnector->isConnected())
                        {
                            $result = $sshConnector->cmd($command,true);
                            
                            $results .= "Log for {$server['name']} : \n" . $result;
                            
                            $sshConnector->disconnect();
                        }
                        $index++;
                    }

                    
                    $message = "$index Server(s) Completed Successfully !";
                    $messageFlag = 'success';
                    
                    $this->setShowMasterView(false);
                    $this->setShowPageView(false);
                    
                    # print the result 
                    die(json_encode(array( "flag" => $messageFlag,'message' => $message,'results' => $results)));
                }
            }
            else    
            {
                # get all servers from the database
                $serverProviders = ServerProvider::all(true,array('status_id = ? ',1),array('id','name'));
                $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','provider_id','main_ip'));

                # get pmta port
                $configuration = new Configuration(array( "type" => "ini" ));
                $result = $configuration->initialize()->parse(Paths::getCurrentApplicationRealPath() . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'pmta',false);
                $pmtaPort = (count($result) > 0 && key_exists('pmta_http_port',$result)) ? $result['pmta_http_port'] : 8080;

                # add data to view 
                $this->getPageView()->set('servers',$servers);
                $this->getPageView()->set('serverProviders',$serverProviders);
                $this->getPageView()->set('pmtaPort',$pmtaPort);

                # check for message 
                PagesHelper::checkForMessageToPage($this);   
            }
        }

        /**
         * @name setMenu
         * @description set the current menu to the template
         * @protected
         */
        public function setMenu() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_pmta',true);
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