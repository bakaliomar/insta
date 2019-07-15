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
    use ma\mailtng\http\Response as Response;
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\www\URL as URL;  
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\http\Request as Request;
    use ma\applications\mailtng\models\admin\Server as Server;
    use ma\applications\mailtng\helpers\StatsHelper as StatsHelper;
    use ma\mailtng\mail\MailBoxManager as MailBoxManager;
    use ma\mailtng\pmta\PmtaHeader as PmtaHeader;
    use ma\mailtng\encryption\Crypto as Crypto;
    use ma\applications\mailtng\models\statistics\Open as Open;
    use ma\applications\mailtng\models\statistics\Click as Click;
    use ma\applications\mailtng\models\statistics\Unsub as Unsub;
    use ma\applications\mailtng\models\statistics\Lead as Lead;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\exceptions\types\PageException as PageException;
    /**
     * @name            Stats.controller 
     * @description     The Stats controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Stats extends Controller 
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
        {
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'stats' . RDS . 'ips.html');
        }
        
        /**
         * @name ips
         * @description ips statistics
         * @before init
         * @after closeConnection,setMenu
         */
        public function ips() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_ips_stats',true);
            
            # get server from database
            $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name'));

            # retrieve all form data
            $formData = Request::getAllDataFromPOST();

            $selectedServers = Arrays::getElement($formData,'servers',array());
            $startDate = Arrays::getElement($formData,'start-date',date('Y-m-d'));
            $endDate = Arrays::getElement($formData,'end-date',date('Y-m-d'));

            # check if a server has been selected 
            $serverCondition = (!empty($selectedServers) && count($selectedServers) > 0) ? " AND di.server_id IN (" . implode(',',$selectedServers) . ")" : "";
       
            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT s.name AS server,"
                                            . " ip.value AS ip,"
                                            . " isp.name AS isp,"
                                            . " di.total_sent AS total,"
                                            . " di.delivered AS delivered,"
                                            . " di.bounced AS bounced,"
                                            . " di.drop_date AS date"
                                            . " FROM production.drop_ips di"
                                            . " LEFT JOIN admin.servers s ON s.id = di.server_id"
                                            . " LEFT JOIN admin.ips ip ON ip.id = di.ip_id"
                                            . " LEFT JOIN admin.isps isp ON isp.id = di.isp_id"
                                            . " WHERE di.drop_date between '$startDate' AND '".date('Y-m-d', strtotime($endDate . ' +1 day'))."'"
                                            . $serverCondition
                                            . " ORDER BY ip.value,di.drop_date ASC", true);
                                    
            $excel = StatsHelper::buildStatsTableForExcel($list);
            
            $table = StatsHelper::buildStatsHTMLTable($list,$startDate,$endDate);

            # get all the columns names 
            $columns = array('server','ip','isp','date','total','delivered','bounced');

            # set the lists list into the template data system 
            $this->getPageView()->set('table',$table);
            
            $this->getPageView()->set('excel',base64_encode($excel));

            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);

            # set date range object to the view
            $this->getPageView()->set('startDate',$startDate);
            $this->getPageView()->set('endDate',$endDate);
            
            # set server object to the view
            $this->getPageView()->set('servers',$servers);
            $this->getPageView()->set('selectedServers',$selectedServers);

            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }
        
        /**
         * @name opens
         * @description opens statistics
         * @before init
         * @after closeConnection,setMenu
         */
        public function opens() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_opens_stats',true);
            
            # retrieve all form data
            $formData = Request::getAllDataFromPOST();
            
            $startDate = Arrays::getElement($formData,'start-date',date('Y-m-d'));
            $endDate = Arrays::getElement($formData,'end-date',date('Y-m-d'));
                
            $columns = array('id','mailer','drop_id','action_date','ip','country','region','city','language','device_type','os','browser_name','browser_version');
            
            # get the connected user
            $user = Session::get('mailtng_connected_user'); 
            
            $condition = " WHERE op.action_date BETWEEN '$startDate' AND '" . date('Y-m-d', strtotime($endDate . ' +1 day')) . "' ";
            $condition .= (!in_array($user['application_role_id'],[1])) ? " AND uc.id = {$user['id']}" : "";
                    
            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT op.*,"
                                            . " uc.username AS mailer"
                                            . " FROM stats.opens op"
                                            . " LEFT JOIN production.drops d ON d.id = op.drop_id"
                                            . " LEFT JOIN admin.users uc ON uc.id = d.user_id"
                                            . $condition
                                            . " ORDER BY op.id DESC", true);
            
            # set the list into the template data system 
            $this->getPageView()->set('list',$list);
            
            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);
            
            # set date range object to the view
            $this->getPageView()->set('startDate',$startDate);
            $this->getPageView()->set('endDate',$endDate);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }
        
        /**
         * @name clicks
         * @description clicks statistics
         * @before init
         * @after closeConnection,setMenu
         */
        public function clicks() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_clicks_stats',true);
            
            # retrieve all form data
            $formData = Request::getAllDataFromPOST();
            
            $startDate = Arrays::getElement($formData,'start-date',date('Y-m-d'));
            $endDate = Arrays::getElement($formData,'end-date',date('Y-m-d'));
            
            $columns = array('id','mailer','drop_id','action_date','ip','country','region','city','language','device_type','os','browser_name','browser_version');
            
            # get the connected user
            $user = Session::get('mailtng_connected_user'); 
            
            $condition = " WHERE c.action_date BETWEEN '$startDate' AND '" . date('Y-m-d', strtotime($endDate . ' +1 day')) . "' ";
            $condition .= (!in_array($user['application_role_id'],[1])) ? " AND uc.id = {$user['id']}" : "";
                    
            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT c.*,"
                                            . " uc.username AS mailer"
                                            . " FROM stats.clicks c"
                                            . " LEFT JOIN production.drops d ON d.id = c.drop_id"
                                            . " LEFT JOIN admin.users uc ON uc.id = d.user_id"
                                            . $condition
                                            . " ORDER BY c.id DESC", true);
            
            # set the list into the template data system 
            $this->getPageView()->set('list',$list);
            
            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);
            
            # set date range object to the view
            $this->getPageView()->set('startDate',$startDate);
            $this->getPageView()->set('endDate',$endDate);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }
        
        /**
         * @name unsubs
         * @description unsubs statistics
         * @before init
         * @after closeConnection,setMenu
         */
        public function unsubs() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_unsubs_stats',true);
            
            # retrieve all form data
            $formData = Request::getAllDataFromPOST();
            
            $startDate = Arrays::getElement($formData,'start-date',date('Y-m-d'));
            $endDate = Arrays::getElement($formData,'end-date',date('Y-m-d'));
            
            $columns = array('id','mailer','drop_id','action_date','ip','country','region','city','language','device_type','os','browser_name','browser_version');
            
            # get the connected user
            $user = Session::get('mailtng_connected_user'); 
            
            $condition = " WHERE u.action_date BETWEEN '$startDate' AND '" . date('Y-m-d', strtotime($endDate . ' +1 day')) . "' ";
            $condition .= (!in_array($user['application_role_id'],[1])) ? " AND uc.id = {$user['id']}" : "";
                    
            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT u.*,"
                                            . " uc.username AS mailer"
                                            . " FROM stats.unsubs u"
                                            . " LEFT JOIN production.drops d ON d.id = u.drop_id"
                                            . " LEFT JOIN admin.users uc ON uc.id = d.user_id"
                                            . $condition
                                            . " ORDER BY u.id DESC", true);
            
            # set the list into the template data system 
            $this->getPageView()->set('list',$list);
            
            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);
            
            # set date range object to the view
            $this->getPageView()->set('startDate',$startDate);
            $this->getPageView()->set('endDate',$endDate);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }
        
        /**
         * @name leads
         * @description leads statistics
         * @before init
         * @after closeConnection,setMenu
         */
        public function leads() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_leads_stats',true);
            
            # retrieve all form data
            $formData = Request::getAllDataFromPOST();
            
            $startDate = Arrays::getElement($formData,'start-date',date('Y-m-d'));
            $endDate = Arrays::getElement($formData,'end-date',date('Y-m-d'));
            
            $columns = array('id','mailer','drop_id','rate','action_date','ip','country','region','city','language','device_type','os','browser_name','browser_version');
            
            # get the connected user
            $user = Session::get('mailtng_connected_user'); 
            
            $condition = " WHERE l.action_date BETWEEN '$startDate' AND '" . date('Y-m-d', strtotime($endDate . ' +1 day')) . "' ";
            $condition .= (!in_array($user['application_role_id'],[1])) ? " AND uc.id = {$user['id']}" : "";
                    
            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT l.*,"
                                            . " uc.username AS mailer"
                                            . " FROM stats.leads l"
                                            . " LEFT JOIN production.drops d ON d.id = l.drop_id"
                                            . " LEFT JOIN admin.users uc ON uc.id = d.user_id"
                                            . $condition
                                            . " ORDER BY l.id DESC", true);
            
            # set the list into the template data system 
            $this->getPageView()->set('list',$list);
            
            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);
            
            # set date range object to the view
            $this->getPageView()->set('startDate',$startDate);
            $this->getPageView()->set('endDate',$endDate);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }
        
        /**
         * @name meta
         * @description meta statistics
         * @before init
         * @after closeConnection,setMenu
         */
        public function meta() 
        { 
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_meta_stats',true);
            
            # retrieve all form data
            $formData = Request::getAllDataFromPOST();

            if(count($formData))
            {
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
            
                $email = Arrays::getElement($formData,'email');
                $password = Arrays::getElement($formData,'password');
                
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
                       $header = $mailbox->getEmailHeader($id);
                       $headerConverter = new PmtaHeader();
                       $headerConverter->convertHeaderTextToParameters($header);
                       $this->updateStatus($headerConverter);
                    }
    
                    # disconnect from inbox 
                    $mailbox->disconnect();
                    
                    # inbox case
                    $mailbox->setFolder('spam');
                    $mailbox->connect($email, $password);
                    $mailbox->sortEmails();
                    $emailsIds = $mailbox->getEmailsIds();
                    
                    foreach ($emailsIds as $id) 
                    {
                       $header = $mailbox->getEmailHeader($id);
                       $headerConverter = new PmtaHeader();
                       $headerConverter->convertHeaderTextToParameters($header);
                       $this->updateStatus($headerConverter);
                    }
                    
                    # disconnect from spam 
                    $mailbox->disconnect();
                }
            }
            
            # stores the message in the session 
            Session::set('proccess_message_flag','success');
            Session::set('proccess_message','Opration Finished Successfully!');

            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }
        
        /**
         * @name updateStatus
         * @description update the status in the database
         * @protected
         */
        protected function updateStatus($header) 
        {
            if(isset($header))
            {
                $date = date("Y-m-d", strtotime(trim($header->getHeaderParameter('Date'))));
                $dropMetadata = $header->getHeaderParameter('x_drop');
  
                if($date == date("Y-m-d") && !empty($dropMetadata))
                {
                    $date = date("Y-m-d",strtotime(trim($header->getHeaderParameter('Date')))); 
                    $dropMetadata = Crypto::AESDecrypt($dropMetadata);
                    $xStoreInfo = $header->getHeaderParameter('x-store-info');
                    $xMessageInfo = $header->getHeaderParameter('X-Message-Info');
                    $xMessageDelivery = $header->getHeaderParameter('X-Message-Delivery'); 
                    //$ip = \ma\applications\mailtng\models\admin\Ip::first(true,array("id = ?",$dropMetadata['ip-id']));
                    
                    # check if this ip has already been updated today 
                    $statusResult = IpStatus::first(true,array("status_date = ? AND ip_id = ?",array($date,$dropMetadata['ip-id'])),array('id','ip_id','status_date'));

                    if(empty($statusResult))
                    {
                        $status = new IpStatus(array(
                            "ip_id" => $dropMetadata['ip-id'],
                            "status_date" => $date,
                            "x_store_info" => $xStoreInfo,
                            "x_message_delivery" => $xMessageDelivery,
                            "x_message_info" => $xMessageInfo    
                        ));
                        
                        $status->save();
                    }
                }
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
            $this->getMasterView()->set('menu_production_stats',true);
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