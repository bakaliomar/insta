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
    use ma\applications\mailtng\models\production\Drop as Drop;
    use ma\applications\mailtng\models\admin\Sponsor as Sponsor;
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\configuration\Configuration as Configuration;
    use ma\mailtng\files\Paths as Paths;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\globals\Server as GloblServers;
    use ma\applications\mailtng\models\admin\Server as Server;
    use ma\mailtng\ssh2\SSH as SSH;
    use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\www\Domains as Domains;
    use ma\mailtng\api\Api as Api;
    use ma\mailtng\exceptions\types\BackendException as BackendException;
    /**
     * @name            Home.controller 
     * @description     The Home controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Home extends Controller 
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
            if(!in_array(Arrays::getElement(Session::get('mailtng_connected_user'),'application_role_id'),array(1)))
            {
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
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
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d',strtotime(date('Y-m-d', time()) . ' + 365 day'));
            
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_sent_stats',true);
           
            # get sum of drops 
            $dropsCount = Drop::count();
            
            # get all the columns names 
            $serversColumns = array('server_name','ssh_status','apache_status','pmta_status','domains_status');
            
            # get num of servers available
            $serversCount = Database::getCurrentDatabaseConnector()->query()->from('admin.servers')->where('status_id = ? AND server_type_id > ?',array(1,1))->count();
            
            # get num of ips available
            $ipsCount = Database::getCurrentDatabaseConnector()->query()->from('admin.ips')->where('status_id = ? AND server_id IN(SELECT id FROM admin.servers WHERE status_id = ? AND server_type_id > ?)',array(1,1,1))->count();
            
            # get num of emails sent
            $emailsCount = Arrays::getElement(Arrays::getElement(Database::getCurrentDatabaseConnector()->executeQuery("SELECT sum(total_sent) FROM production.drop_ips WHERE drop_date between '$startDate' AND '$endDate'",true),0,array()),'sum',0);
            
            # get delivery total
            $deliveryCount = Arrays::getElement(Arrays::getElement(Database::getCurrentDatabaseConnector()->executeQuery("SELECT sum(delivered) FROM production.drop_ips WHERE drop_date between '$startDate' AND '$endDate'",true),0,array()),'sum',0);
            
            # get bounce total
            $bounceCount = Arrays::getElement(Arrays::getElement(Database::getCurrentDatabaseConnector()->executeQuery("SELECT sum(bounced) FROM production.drop_ips WHERE drop_date between '$startDate' AND '$endDate'",true),0,array()),'sum',0);

            # get in progress drops
            $dropsColumns = array('id','mailer','server','total','start_time');

            # check if a server has been selected 
            $userCondition = (!in_array(Arrays::getElement(Session::get('mailtng_connected_user'),'application_role_id'),array(1))) ? " AND d.user_id = " . Arrays::getElement(Session::get('mailtng_connected_user'),'id') : "";
    
            # get the data from the database
            $drops = Database::getCurrentDatabaseConnector()->executeQuery("SELECT d.id AS id,"
                                            . " u.username AS mailer,"
                                            . " d.status AS status,"
                                            . " d.total_emails AS total,"
                                            . " s.name AS server,"
                                            . " to_char(d.start_time,'DD-MM-YYYY HH24:MI:SS') AS start_time"
                                            . " FROM production.drops d"
                                            . " LEFT JOIN admin.users u ON u.id = d.user_id"
                                            . " LEFT JOIN admin.servers s ON s.id = d.server_id"
                                            . " WHERE d.start_time between '$startDate' AND '$endDate'"
                                            . $userCondition
                                            . " AND status = 'in-progress'"
                                            . " ORDER BY d.id DESC", true);
            
            # set data to the view
            $this->getPageView()->set('columns',$serversColumns);
            $this->getPageView()->set('serversCount',$serversCount);
            $this->getPageView()->set('ipsCount',intval($ipsCount));
            $this->getPageView()->set('dropsCount',intval($dropsCount));
            $this->getPageView()->set('emailsCount',intval($emailsCount));
            $this->getPageView()->set('deliveryCount',intval($deliveryCount));
            $this->getPageView()->set('bounceCount',intval($bounceCount));
            $diff = intval($emailsCount) - (intval($deliveryCount) + intval($bounceCount));
            $this->getPageView()->set('differenceCount',$diff);
            $this->getPageView()->set('dropsColumns',$dropsColumns);
            $this->getPageView()->set('drops',$drops);

            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }

        /**
         * @name getServersMonitor
         * @description gets servers monitor into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getServersMonitor() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $result = array();
                
                # get the server from the database
                $servers = Server::all(true,array('server_type_id != ? AND status_id = ?',array(1,1)));

                if(isset($servers) && count($servers))
                {
                    # get pmta port
                    $configuration = new Configuration(array( "type" => "ini" ));
                    $pmtaResult = $configuration->initialize()->parse(Paths::getCurrentApplicationRealPath() . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'pmta',false);
                    $pmtaPort = (count($pmtaResult) > 0 && key_exists('pmta_http_port',$pmtaResult)) ? $pmtaResult['pmta_http_port'] : 8080;
                    
                    foreach ($servers as $server) 
                    {
                        $row = array();
                        $row[] = $server['name'];
                        
                        try 
                        {
                            $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                            $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);
                            
                            if($sshConnector->isConnected())
                            {
                                $row[] = "<center><span style='color:#4db3a2;padding:5px'>Online</span></center>";
                                $version = 6;
                                $release = $sshConnector->cmd("cat /etc/centos-release",true);
                        
                                if(strpos($release,'CentOS Linux release 7') > -1)
                                {
                                    $version = 7;
                                }
                        
                                $command = $version == 7 ? "systemctl status httpd" : "service httpd status";
                                $httpdstatus = $sshConnector->cmd($command,true);
                                
                                if(Strings::indexOf($httpdstatus,"running") > 0)
                                {
                                    $row[] = "<center><span style='color:#4db3a2;padding:5px'><a style='color:#4db3a2;' href='http://" . gethostbyaddr($server['main_ip']) . "' target='apache_{$server['id']}' >Online</a></span></center>";
                                }
                                else
                                {
                                    $row[] = "<center><span style='color:#e7505a;padding:5px'><a style='color:#e7505a;' href='http://" . gethostbyaddr($server['main_ip']) . "' target='apache_{$server['id']}' >Offline</a></span></center>";
                                }

                                $command = $version == 7 ? "systemctl status pmta" : "service pmta status";
                                $pmtastatus = $sshConnector->cmd($command,true);
                                $command = $version == 7 ? "systemctl status pmtahttp" : "service pmtahttp status";
                                $pmtahttpstatus = $sshConnector->cmd("service pmtahttp status",true);

                                if(Strings::indexOf($pmtastatus,"running") > 0 && Strings::indexOf($pmtahttpstatus,"running") > 0)
                                {
                                    $row[] = "<center><span style='color:#4db3a2;padding:5px'><a style='color:#4db3a2;' href='http://{$server['main_ip']}:{$pmtaPort}' target='apache_{$server['id']}' >Online</a></span></center>";
                                }
                                else
                                {
                                    $row[] = "<center><span style='color:#e7505a;padding:5px'><a style='color:#e7505a;' href='http://{$server['main_ip']}:{$pmtaPort}' target='apache_{$server['id']}' >Offline</a></span></center>";
                                }

                                $row[] = "<center><a href='#body-modal' role='button' data-toggle='modal' class='domains-check' data-server-id='{$server['id']}' >Check Domains</a></center>";
                                $sshConnector->disconnect();
                            }
                            else
                            {
                                $row[] = "<center><span style='color:#e7505a;padding:5px'>Offline</span></center>";
                                $row[] = "<center><span style='color:#e7505a;padding:5px'>Offline</span></center>";
                                $row[] = "<center><span style='color:#e7505a;padding:5px'>Offline</span></center>";
                                $row[] = "<center><a href='#body-modal' role='button' data-toggle='modal' class='domains-check' data-server-id='{$server['id']}' >Check Domains</a></center>";
                            }
                        } 
                        catch (BackendException $e) 
                        {
                            # log the message error
                            Logger::error($e);
                            
                            $row[] = "<center><span style='color:#e7505a;padding:5px'>Offline</span></center>";
                            $row[] = "<center><span style='color:#e7505a;padding:5px'>Offline</span></center>";
                            $row[] = "<center><span style='color:#e7505a;padding:5px'>Offline</span></center>";
                            $row[] = "<center><a href='#body-modal' role='button' data-toggle='modal' class='domains-check' data-server-id='{$server['id']}' >Check Domains</a></center>";
                        }
                        
                        $result[] = $row;
                    }
                }
                
                die(json_encode(array("data" => $result)));
            }
        }
        
        /**
         * @name getDomainsStatus
         * @description gets the domains status into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDomainsStatus() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
            
                $serverId = isset($arguments) && count($arguments) ? intval($arguments[0]) : null;
            
                $table = "";
                
                if($serverId > 0)
                {
                    $domainsList = Database::getCurrentDatabaseConnector()->executeQuery("SELECT d.id AS id,"
                                                . " d.value AS domain_value,"
                                                . " ip.value AS ip_value"
                                                . " FROM admin.domains d"
                                                . " LEFT JOIN admin.ips ip ON ip.id = d.ip_id"
                                                . " LEFT JOIN admin.status st ON st.id = d.status_id"
                                                . " WHERE d.ip_id IN (SELECT id FROM admin.ips WHERE server_id = $serverId)"
                                                . " ORDER BY d.id", true);
                    
                    if(isset($domainsList) && count($domainsList))
                    {
                        $table = "<table class='table table-bordered table-striped table-condensed'>";
                        $table .= "<thead><tr>";
                        $table .= "<td>Domain Name</td><td>Connection Status</td><td>SpamHouse DBL Status</td>";
                        $table .= "</tr></thead>";
                        $table .= "<tbody>";
                        foreach ($domainsList as $domain) 
                        {
                            $table .= "<tr>";
                            $table .= "<td>{$domain['domain_value']}</td>";

                            if(Domains::isValidDomain($domain['domain_value']))
                            {
                                if(Domains::isTimedOut($domain['domain_value'],20)) 
                                {
                                    $table .= "<td><center><span style='color:#e7505a;padding:5px'> Offline </span></center></td>";
                                }
                                else
                                {
                                    $table .= "<td><center><span style='color:#4db3a2;padding:5px'> Connected </span></center></td>";
                                }
                                
                                if(Domains::isDBL($domain['domain_value'])) 
                                {
                                    $table .= "<td><center><span style='color:#e7505a;padding:5px'> Listed </span></center></td>";
                                }
                                else
                                {
                                    $table .= "<td><center><span style='color:#4db3a2;padding:5px'> Not Listed </span></center></td>";
                                }
                            }
                            else
                            {
                                $table .= "<td><center><span style='color:#e7505a;padding:5px'> Not Valid Domain</span></center></td>";
                                $table .= "<td><center><span style='color:#e7505a;padding:5px'> Not Valid Domain</span></center></td>";
                            }

                            $table .= "</tr>";
                        }
                         $table .= "</tbody></table>";
                    }
                }

                die($table);
            }
        }
        
        /**
         * @name getEarnings
         * @description gets earnings report into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getEarnings() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $earnings = 0.0;
                $sponsors = Sponsor::all(true,array('status_id = ?',1));

                if (count($sponsors))
                {
                    foreach ($sponsors as $sponsor) 
                    {
                        $api = null;
                        
                        if(count($sponsor))
                        {      
                            $api = Api::getAPIClass($sponsor);
                        }

                        if($api != null)
                        {
                            $earnings += $api->getMonthEarning();
                        }   
                    }
                }
                
            
                die(json_encode(array('earnings' => number_format($earnings,2,',','.'))));
            }
        }
        
        /**
         * @name getDailySentReport
         * @description gets daily sent report into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDailySentReport() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                # get daily sent report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('day',drop_date) as day,date_part('month',drop_date)  as month ,SUM(total_sent) AS total FROM production.drop_ips WHERE date_trunc('month', drop_date) = date_trunc('month', current_date) AND date_trunc('year', drop_date) = date_trunc('year', current_date) GROUP BY date_part('day',drop_date), date_part('month',drop_date) ORDER BY date_part('day',drop_date), date_part('month',drop_date)",true);
                $sent = array();
                
                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($sent,array($row['day'],$row['total']));
                    }
                }

                # get daily delivery report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('day',drop_date) as day,date_part('month',drop_date)  as month ,SUM(delivered) AS total FROM production.drop_ips WHERE date_trunc('month', drop_date) = date_trunc('month', current_date) AND date_trunc('year', drop_date) = date_trunc('year', current_date) GROUP BY date_part('day',drop_date), date_part('month',drop_date) ORDER BY date_part('day',drop_date), date_part('month',drop_date)",true);
                $delivery = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($delivery,array($row['day'],$row['total']));
                    }
                }

                # get daily bounce report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('day',drop_date) as day,date_part('month',drop_date)  as month ,SUM(bounced) AS total FROM production.drop_ips WHERE date_trunc('month', drop_date) = date_trunc('month', current_date) AND date_trunc('year', drop_date) = date_trunc('year', current_date) GROUP BY date_part('day',drop_date), date_part('month',drop_date) ORDER BY date_part('day',drop_date), date_part('month',drop_date)",true);
                $bounce = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($bounce,array($row['day'],$row['total']));
                    }
                }
            
                die(json_encode(array('sent' => $sent,'delivery' => $delivery,'bounce' => $bounce)));
            }
        }
        
        /**
         * @name getDailyActionsReport
         * @description gets daily actions report into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDailyActionsReport() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # get daily opens report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('day',action_date) as day,date_part('month',action_date)  as month ,COUNT(id) AS total FROM stats.opens WHERE date_trunc('month', action_date) = date_trunc('month', current_date) AND date_trunc('year', action_date) = date_trunc('year', current_date) GROUP BY date_part('day',action_date), date_part('month',action_date) ORDER BY date_part('day',action_date), date_part('month',action_date)",true);
                $opens = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($opens,array($row['day'],$row['total']));
                    }
                }

                # get daily opens report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('day',action_date) as day,date_part('month',action_date)  as month ,COUNT(id) AS total FROM stats.clicks WHERE date_trunc('month', action_date) = date_trunc('month', current_date) AND date_trunc('year', action_date) = date_trunc('year', current_date) GROUP BY date_part('day',action_date), date_part('month',action_date) ORDER BY date_part('day',action_date), date_part('month',action_date)",true);
                $clicks = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($clicks,array($row['day'],$row['total']));
                    }
                }

                # get daily opens report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('day',action_date) as day,date_part('month',action_date)  as month ,COUNT(id) AS total FROM stats.leads WHERE date_trunc('month', action_date) = date_trunc('month', current_date) AND date_trunc('year', action_date) = date_trunc('year', current_date) GROUP BY date_part('day',action_date), date_part('month',action_date) ORDER BY date_part('day',action_date), date_part('month',action_date)",true);
                $leads = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($leads,array($row['day'],$row['total']));
                    }
                }

                # get daily opens report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('day',action_date) as day,date_part('month',action_date)  as month ,COUNT(id) AS total FROM stats.unsubs WHERE date_trunc('month', action_date) = date_trunc('month', current_date) AND date_trunc('year', action_date) = date_trunc('year', current_date) GROUP BY date_part('day',action_date), date_part('month',action_date) ORDER BY date_part('day',action_date), date_part('month',action_date)",true);
                $unsubs = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($unsubs,array($row['day'],$row['total']));
                    }
                }
            
                die(json_encode(array('opens' => $opens,'clicks' => $clicks,'leads' => $leads,'unsubs' => $unsubs)));
            }
        }
        
        /**
         * @name getMonthlySentReport
         * @description gets monthly sent report into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getMonthlySentReport() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                # get delivered report
                $results = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('year',drop_date) as year,date_part('month',drop_date)  as month ,SUM(total_sent) AS total FROM production.drop_ips WHERE date_trunc('year', drop_date) = date_trunc('year', current_date) GROUP BY date_part('year',drop_date), date_part('month',drop_date) ORDER BY date_part('year',drop_date), date_part('month',drop_date)",true);
                $sent = array();

                if (count($results))
                {
                    foreach ($results as $row) 
                    {
                        array_push($sent,array($row['month'],$row['total']));
                    }
                }

                # get delivered report
                $results = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('year',drop_date) as year,date_part('month',drop_date)  as month ,SUM(delivered) AS total FROM production.drop_ips WHERE date_trunc('year', drop_date) = date_trunc('year', current_date) GROUP BY date_part('year',drop_date), date_part('month',drop_date) ORDER BY date_part('year',drop_date), date_part('month',drop_date)",true);
                $delivery = array();

                if (count($results))
                {
                    foreach ($results as $row) 
                    {
                        array_push($delivery,array($row['month'],$row['total']));
                    }
                }

                # get bounce report
                $results = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('year',drop_date) as year,date_part('month',drop_date)  as month ,SUM(bounced) AS total FROM production.drop_ips WHERE date_trunc('year', drop_date) = date_trunc('year', current_date) GROUP BY date_part('year',drop_date), date_part('month',drop_date) ORDER BY date_part('year',drop_date), date_part('month',drop_date)",true);
                $bounce = array();

                if (count($results))
                {
                    foreach ($results as $row) 
                    {
                        array_push($bounce,array($row['month'],$row['total']));
                    }
                }
            
                die(json_encode(array('sent' => $sent,'delivery' => $delivery,'bounce' => $bounce)));
            }
        }
       
        /**
         * @name getMonthlyActionsReport
         * @description gets monthly actions report into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getMonthlyActionsReport() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # get opens report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('year',action_date) as year,date_part('month',action_date) as month ,COUNT(id) AS total FROM stats.opens WHERE date_trunc('year', action_date) = date_trunc('year', current_date) GROUP BY date_part('year',action_date), date_part('month',action_date) ORDER BY date_part('year',action_date), date_part('month',action_date)",true);
                $opens = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($opens,array($row['month'],$row['total']));
                    }
                }

                # get clicks report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('year',action_date) as year,date_part('month',action_date) as month ,COUNT(id) AS total FROM stats.clicks WHERE date_trunc('year', action_date) = date_trunc('year', current_date) GROUP BY date_part('year',action_date), date_part('month',action_date) ORDER BY date_part('year',action_date), date_part('month',action_date)",true);
                $clicks = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($clicks,array($row['month'],$row['total']));
                    }
                }

                # get leads report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('year',action_date) as year,date_part('month',action_date) as month ,COUNT(id) AS total FROM stats.leads WHERE date_trunc('year', action_date) = date_trunc('year', current_date) GROUP BY date_part('year',action_date), date_part('month',action_date) ORDER BY date_part('year',action_date), date_part('month',action_date)",true);
                $leads = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($leads,array($row['month'],$row['total']));
                    }
                }

                # get unsubs report
                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT date_part('year',action_date) as year,date_part('month',action_date) as month ,COUNT(id) AS total FROM stats.unsubs WHERE date_trunc('year', action_date) = date_trunc('year', current_date) GROUP BY date_part('year',action_date), date_part('month',action_date) ORDER BY date_part('year',action_date), date_part('month',action_date)",true);
                $unsubs = array();

                if (count($result))
                {
                    foreach ($result as $row) 
                    {
                        array_push($unsubs,array($row['month'],$row['total']));
                    }
                }
            
                die(json_encode(array('opens' => $opens,'clicks' => $clicks,'leads' => $leads,'unsubs' => $unsubs)));
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
            $this->getMasterView()->set('menu_home',true);
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