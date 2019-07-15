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
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\globals\Server as GloblServers;
    use ma\mailtng\ssh2\SSH as SSH;
    use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
    use ma\applications\mailtng\models\admin\Server as Server;
    use ma\applications\mailtng\models\production\Drop as Drop;
    use ma\applications\mailtng\models\admin\Sponsor as Sponsor;
    use ma\applications\mailtng\models\admin\Offer as Offer;
    use ma\applications\mailtng\models\admin\OfferFromName as OfferFromName;
    use ma\applications\mailtng\models\admin\OfferSubject as OfferSubject;
    use ma\applications\mailtng\models\admin\Isp as Isp;
    use ma\mailtng\encryption\Crypto as Crypto;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\os\System as System;
    /**
     * @name            Drops.controller 
     * @description     The Drops controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Drops extends Controller 
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
        }

        /**
         * @name index
         * @description the index action
         * @before init
         * @after setMenu,closeConnection
         */
        public function index() 
        {
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'drops' . RDS . 'lists.html');
        }
        
        /**
         * @name lists
         * @description the lists action
         * @before init
         * @after setMenu,closeConnection
         */
        public function lists() 
        {   
            $arguments = func_get_args(); 
            
            $serverId = isset($arguments) && count($arguments) ? $arguments[0] : null;

            if(isset($serverId) && is_numeric($serverId))
            {
                # get server from database
                $server = Server::first(true,array('id = ?',$serverId));
                
                # set server object to the view
                $this->getPageView()->set('server',$server);
            }
            
            $startDate = date('Y-m-d');
            $endDate =  date('Y-m-d',strtotime(date('Y-m-d', mktime()) . ' + 1 day'));
            
            # retrieve all form data
            $formData = Request::getAllDataFromPOST();

            if(count($formData))
            {
                $startDate = Arrays::getElement($formData,'start-date',date('Y-m-d'));
                $endDate = date('Y-m-d',strtotime(Arrays::getElement($formData,'end-date',date('Y-m-d')) . ' + 1 day'));
            }
            
            
            # check if a server has been selected 
            $userCondition = (!in_array(Arrays::getElement(Session::get('mailtng_connected_user'),'application_role_id'),array(1))) ? " AND d.user_id = " . Arrays::getElement(Session::get('mailtng_connected_user'),'id') : "";
            
            # check if a server has been selected 
            $serverCondition = (!empty($serverId) && intval($serverId) > 0) ? " AND server_id = $serverId" : "";
            
            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT d.id AS id,"
                                            . " u.username AS mailer,"
                                            . " d.status AS status,"
                                            . " d.total_emails AS total,"
                                            . " s.name AS server,"
                                            . " s.id AS server_id,"
                                            . " s.main_ip AS server_ip,"
                                            . " d.sent_progress AS progress,"
                                            . " to_char(d.start_time,'DD-MM-YYYY HH24:MI:SS') AS start_time,"
                                            . " to_char(d.finish_time,'DD-MM-YYYY HH24:MI:SS') AS finish_time,"
                                            . " d.header AS header"
                                            . " FROM production.drops d"
                                            . " LEFT JOIN admin.users u ON u.id = d.user_id"
                                            . " LEFT JOIN admin.servers s ON s.id = d.server_id"
                                            . " WHERE d.start_time between '$startDate' AND '$endDate'"
                                            . $serverCondition
                                            . $userCondition
                                            . " ORDER BY d.id DESC", true);
                                    
            # get all the columns names 
            $columns = array('id','mailer','server','status','total','progress','start_time','finish_time');

            # set the lists list into the template data system 
            $this->getPageView()->set('list',$list);

            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);

            # set date range object to the view
            $this->getPageView()->set('startDate',$startDate);
            $this->getPageView()->set('endDate',$endDate);

            # get pmta port
            $configuration = new Configuration(array( "type" => "ini" ));
            $result = $configuration->initialize()->parse(Paths::getCurrentApplicationRealPath() . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'pmta',false);
            $pmtaPort = (count($result) > 0 && key_exists('pmta_http_port',$result)) ? $result['pmta_http_port'] : 8080;
            
            # set pmta port into the template data system 
            $this->getPageView()->set('pmtaPort',$pmtaPort);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        } 
        
        /**
         * @name logs
         * @description the logs action
         * @before init
         * @after setMenu,closeConnection
         */
        public function logs() 
        {   
            $arguments = func_get_args(); 
            
            $logType = isset($arguments) && count($arguments) ? $arguments[0] : null;
            $serverId = isset($arguments) && count($arguments) ? $arguments[1] : null;
            $dropId = isset($arguments) && count($arguments) ? $arguments[2] : null;
            
            # retrieve all form data
            $formData = Request::getAllDataFromPOST();

            $startDate = Arrays::getElement($formData,'start-date',date('Y-m-d'));
            $endDate = Arrays::getElement($formData,'end-date',date('Y-m-d'));
            $dateDifferenceObject = date_diff(date_create($endDate),date_create($startDate));
            $diff = (isset($dateDifferenceObject) && is_object($dateDifferenceObject)) ?  intval($dateDifferenceObject->days) : 0;

            if(isset($logType) && isset($serverId) && is_numeric($serverId))
            {  
                # get server from database
                $server = Server::first(true,array('id = ?',$serverId),array('id','name','main_ip','username','password','ssh_port'));
                
                $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                if($sshConnector->isConnected())
                {                   
                    if($logType == 'recalculate')
                    {
                        $sshConnector->cmd("php /usr/mailtng/scripts/mails_calculation/move_accounting_files.php; php /usr/mailtng/scripts/mails_calculation/read_accounting_files.php;");
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'drops' . RDS . 'logs' . RDS . 'delivery' . RDS . $serverId . '.html');
                    }
                    else
                    {
                        $date = date('Y-m-d');
                        $results = array();
                        $columns = array('ip','type','drop_time','status','message');
                        $folder = $logType == 'delivery' ? 'delivered' : 'bounces';
                        
                        if($diff > 0)
                        {
                            $command = "cat ";  

                            for ($date = strtotime($startDate); $date <= strtotime($endDate); $date = strtotime("+1 day", $date)) 
                            {
                                $command .= "/etc/pmta/$folder/moved/" . date("Y-m-d", $date) . "/b-*.csv ";
                            }
                        }
                        else
                        {
                            $command = "cat /etc/pmta/$folder/moved/$date/b-*.csv";   
                        }

                        $content = explode(PHP_EOL,$sshConnector->cmd($command,true));
                        $logs = array_map('str_getcsv',array_filter($content, function($var) { return (trim($var) != "type,bounceCat,timeLogged,rcpt,dsnAction,dsnStatus,dsnDiag,dlvSourceIp,header_drop_meta"); }));

                        if(isset($logs) && count($logs) > 0)
                        {
                            foreach ($logs as $line) 
                            {
                                if(count($line) == 9)
                                {
                                    $params = Crypto::AESDecrypt($line[8]);

                                    if(!empty($params) && strpos($params,'|') > 1)
                                    {
                                        $data = array(
                                            "ip" => $line[7],
                                            "type" => $line[1],
                                            "drop_time" => date('Y-m-d H:i:s',strtotime(trim($line[2]))),
                                            "status" => $line[4],
                                            "message" => $line[6]
                                        );
                                        
                                        if(isset($dropId) && intval($dropId) > 0)
                                        {
                                            $dropIdFromLog = Arrays::getElement(explode('|',$params),0);

                                            if(intval(trim($dropIdFromLog)) == intval(trim($dropId)))
                                            {
                                                $results[] = $data;
                                            }
                                        }
                                        else
                                        {
                                            $results[] = $data;
                                        }
                                    } 
                                }
                            }
                        }
                        
                        # set the lists list into the template data system 
                        $this->getPageView()->set('list',$results);

                        # set the columns list into the template data system 
                        $this->getPageView()->set('columns',$columns);

                        # set the columns list into the template data system 
                        $this->getPageView()->set('type',ucfirst($logType));
                    }
                    
                    # disconnect from the server
                    $sshConnector->disconnect();
                }

                # set server object to the view
                $this->getPageView()->set('server',$server);
                $this->getPageView()->set('drop',$dropId);
            }

            # set date range object to the view
            $this->getPageView()->set('startDate',$startDate);
            $this->getPageView()->set('endDate',$endDate);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        } 
        
        /**
         * @name getDrop
         * @description get drop by id
         * @before init
         * @after setMenu,closeConnection
         */
        public function getDrop() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                if($this->getDefaultContentType() == 'text/json')
                {
                    $arguments = func_get_args(); 
                    $dropId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                    if(isset($dropId) && is_numeric($dropId) && $this->getDefaultContentType() == 'text/json')
                    {
                        $drop = Drop::first(true,array('id=?',$dropId));
                        $html = '';
                        
                        if(count($drop))
                        {
                            $dropData = json_decode(base64_decode($drop['post_data']),true);
                            $isp = Isp::first(true,array('id = ?',intval(Arrays::getElement($dropData,'isp-id',1))));
                            $sponsor = Sponsor::first(true,array('id = ?',intval(Arrays::getElement($dropData,'sponsor',0))));
                            $offer = Offer::first(true,array('id = ?',intval(Arrays::getElement($dropData,'offer'))));
                            $fromName = OfferFromName::first(true,array('id = ?',intval(Arrays::getElement($dropData,'from-name-id'))));
                            $subject = OfferSubject::first(true,array('id = ?',intval(Arrays::getElement($dropData,'subject-id'))));
        
                            $html = str_replace(array(
                                '{id}',
                                '{status}',
                                '{total}',
                                '{progress}',
                                '{start-time}',
                                '{finish-time}',
                                '{isp}',
                                '{sponsor}',
                                '{offer}',
                                '{fromname}',
                                '{subject}',
                                '{lists}',
                                '{receipients}',
                                '{header}',
                                '{body}'
                            ),array(
                                $drop['id'],
                                $drop['status'],
                                $drop['total_emails'],
                                $drop['sent_progress'],
                                $drop['start_time'],
                                $drop['finish_time'],
                                $isp['name'],
                                $sponsor['name'],
                                $offer['name'],
                                $fromName['value'],
                                $subject['value'],
                                str_replace(',',"\n",$drop['lists']),
                                str_replace(',',"\n",$drop['recipients_emails']),
                                base64_decode($drop['header']),
                                $dropData['body']
                            )
                            ,'<div class="form-body"><div class="row"><div class="col-md-2"><div class="form-group"><label class="control-label">Drop Id</label><input type="text" class="form-control" readonly value="{id}"></div></div><div class="col-md-2"><div class="form-group"><label class="control-label">Drop Status</label><input type="text" class="form-control" readonly value="{status}"></div></div><div class="col-md-2"><div class="form-group"><label class="control-label">Total Emails</label><input type="text" class="form-control" readonly value="{total}"></div></div><div class="col-md-2"><div class="form-group"><label class="control-label">Emails Sent</label><input type="text" class="form-control" readonly value="{progress}"></div></div><div class="col-md-2"><div class="form-group"><label class="control-label">Start Time</label><input type="text" class="form-control" readonly value="{start-time}"></div></div><div class="col-md-2"><div class="form-group"><label class="control-label">Finish Time</label><input type="text" class="form-control" readonly value="{finish-time}"></div></div></div><div class="row"><div class="col-md-4"><div class="form-group"><label class="control-label">ISP Name</label><input type="text" class="form-control" readonly value="{isp}"></div></div><div class="col-md-4"><div class="form-group"><label class="control-label">Sponsor Name</label><input type="text" class="form-control" readonly value="{sponsor}"></div></div><div class="col-md-4"><div class="form-group"><label class="control-label">Offer Name</label><input type="text" class="form-control" readonly value="{offer}"></div></div></div> 	<div class="row"><div class="col-md-6"><div class="form-group"><label class="control-label">From Name</label><input type="text" class="form-control" readonly value="{fromname}"></div></div><div class="col-md-6"><div class="form-group"><label class="control-label">Subject</label><input type="text" class="form-control" readonly value="{subject}"></div></div> 	</div><div class="row"><div class="col-md-6"><div class="form-group"><label class="control-label">Header</label><textarea class="form-control" rows="10" spellcheck="false" wrap="off" readonly>{header}</textarea></div></div><div class="col-md-3"><div class="form-group"><label class="control-label">Receipients Emails</label><textarea class="form-control" rows="10" spellcheck="false" wrap="off" readonly>{receipients}</textarea></div></div><div class="col-md-3"><div class="form-group"><label class="control-label">Lists</label><textarea class="form-control" rows="10" spellcheck="false" wrap="off" readonly>{lists}</textarea></div></div></div><div class="row"><div class="col-md-12"><div class="form-group"><label class="control-label">Body</label><textarea class="form-control" rows="20" spellcheck="false" wrap="off" readonly>{body}</textarea></div></div></div></div>');
                        }
                       
                        die(json_encode(array( "drop" => $html)));
                    }
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'drops' . RDS . 'lists.html');
            }
        }
        
        /**
         * @name stop
         * @description stop the drop 
         * @before init
         * @after setMenu,closeConnection
         */
        public function stop() 
        {
            $arguments = func_get_args(); 
            $dropId = isset($arguments) && count($arguments) ? $arguments[0] : null;

            if(isset($dropId) && is_numeric($dropId))
            {
                $drop = Drop::first(true,array('id = ?',$dropId));
                
                if(isset($drop) && $drop['status'] == 'in-progress')
                {
                    $proccessIds = explode(',',$drop['pids']);
                    
                    foreach ($proccessIds as $pid)
                    {
                        # kill the proccess
                        System::executeCommand("kill -9 $pid");
                    }

                    # update the status and finish time 
                    $finishTime = date('Y-m-d H:i:s');
                    Database::getCurrentDatabaseConnector()->executeQuery("UPDATE production.drops SET status = 'interrupted' , finish_time = '$finishTime' WHERE id = {$dropId}");
                } 
            }
            
            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'drops' . RDS . 'lists.html');
        }
        
        /**
         * @name recalculate
         * @description recalculates the sent progress
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function recalculate() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                if($this->getDefaultContentType() == 'text/json')
                {
                    $sent = 0;
                    $arguments = func_get_args(); 
                    $dropId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                    if(isset($dropId) && is_numeric($dropId) && $this->getDefaultContentType() == 'text/json')
                    {
                        $drop = Drop::first(true,array('id = ?',$dropId));
                        
                        if(isset($drop) && $drop['status'] == 'in-progress')
                        {
                            $sent = intval($drop['sent_progress']);
                        } 
                    } 
                    
                    die(json_encode(array( "sentProgress" => $sent)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'drops' . RDS . 'lists.html');
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
            $this->getMasterView()->set('menu_production_drops',true);
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