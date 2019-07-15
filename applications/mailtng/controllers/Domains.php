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
    use ma\mailtng\http\Response as Response;
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\www\URL as URL;  
    use ma\mailtng\types\Arrays as Arrays;
    use ma\applications\mailtng\models\admin\Domain as Domain;
    use ma\applications\mailtng\models\admin\Ip as Ip;
    use ma\mailtng\www\Domains as DomainsHelper;
    use ma\applications\mailtng\models\admin\Isp as Isp;
    use ma\applications\mailtng\models\admin\Status as Status;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\api\NameCheap as NameCheap;
    use ma\mailtng\exceptions\types\PageException as PageException;
    /**
     * @name            Domains.controller 
     * @description     The Domains controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Domains extends Controller 
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
        {
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'domains' . RDS . 'lists.html');
        }
        
        /**
         * @name lists
         * @description the lists action
         * @before init
         * @after setMenu,closeConnection
         */
        public function lists() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_domains',true);
            $this->getMasterView()->set('menu_admin_domains_lists',true);

            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT d.id AS id,"
                                            . " d.value AS domain_name,"
                                            . " s.name AS status,"
                                            . " d.domain_status AS domain_status,"
                                            . " i.value AS domain_ip,"
                                            . " uc.username AS created_by,"
                                            . " d.created_at AS created_date,"
                                            . " up.username AS last_updated_by,"
                                            . " d.last_updated_at AS last_updated_at"
                                            . " FROM admin.domains d"
                                            . " LEFT JOIN admin.users uc ON uc.id = d.created_by"
                                            . " LEFT JOIN admin.ips i ON i.id = d.ip_id"
                                            . " LEFT JOIN admin.status s ON s.id = d.status_id"
                                            . " LEFT JOIN admin.users up ON up.id = d.last_updated_by"
                                            . " ORDER BY d.id", true);
                                    
            # get all the columns names 
            $columns = array('id','domain_name','status','domain_status','domain_ip','created_by','created_date','last_updated_by','last_updated_at');

            # set the list into the template data system 
            $this->getPageView()->set('list',$list);
            
            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);

            # check for message 
            PagesHelper::checkForMessageToPage($this);
        } 
        
        /**
         * @name add
         * @description the add action
         * @before init
         * @after setMenu,closeConnection
         */
        public function add() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_domains',true);
            $this->getMasterView()->set('menu_admin_domains_add',true);
            
            # get status list 
            $status = Status::all(true,array(),array('id','name'),'id','ASC');

            # set the list into the template data system 
            $this->getPageView()->set('status',$status);
        }
        
        /**
         * @name add
         * @description the add action
         * @before init
         * @after setMenu,closeConnection
         */
        public function edit() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_domains',true);
            $this->getMasterView()->set('menu_admin_domains_add',true);
            
            $arguments = func_get_args(); 
            $id = isset($arguments) && count($arguments) ? $arguments[0] : null;

            if(isset($id) && is_numeric($id))
            {
                # retrieve the server by id
                $isp = Isp::first(true,array('id = ?',$id));
                $status = Status::all(true);

                # set the data to the template
                $this->getPageView()->set('isp',$isp);
                $this->getPageView()->set('status',$status);
            }
        }
        
        /**
         * @name save
         * @description the save action
         * @before init
         * @after setMenu,closeConnection
         */
        public function save() 
        {     
            # get the connected user
            $user = Session::get('mailtng_connected_user'); 

            $message = "Something went wrong !";
            $messageFlag = 'error';

            $domainsList = explode(PHP_EOL,Request::getParameterFromPOST('domains'));
            $ips = Ip::all(true);
            $domainsResult = Domain::all(true);
            $domainsCheckArray = array();

            if(count($domainsResult))
            {
                foreach ($domainsResult as $domainsResultLine) 
                {
                    $domainsCheckArray[] = trim($domainsResultLine['value']);
                }
            }

            if(is_array($domainsList) && count($domainsList))
            {
                foreach ($domainsList as $domain) 
                {
                    $domain = preg_replace( "/\r|\n/","",trim($domain));

                    if(DomainsHelper::isValidDomain($domain) && !in_array(trim($domain), $domainsCheckArray))
                    {
                        # insert case
                        $domainObject = new Domain();
                        $domainObject->setStatus_id(1);
                        $domainObject->setValue($domain);

                        $found = false;

                        if(is_array($ips) && count($ips))
                        {
                            foreach ($ips as $ip) 
                            {
                                if(trim($ip['rdns']) == trim($domain))
                                {
                                    $domainObject->setIp_id($ip['id']);
                                    $domainObject->setDomain_status("Taken");
                                    $found = true;
                                }
                            }
                        }

                        if(!$found)
                        {
                            $domainObject->setIp_id(0);
                            $domainObject->setDomain_status("Available");
                        }

                        $domainObject->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                        $domainObject->setCreated_at(date("Y-m-d"));
                        $domainObject->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                        $domainObject->setLast_updated_at(date("Y-m-d"));

                        $result = $domainObject->save();  

                        if($result > -1)
                        {
                            $message = "Records stored succesfully !";
                            $messageFlag = 'success';
                        }
                    }
                }
            }

            # stores the message in the session 
            Session::set('proccess_message_flag',$messageFlag);
            Session::set('proccess_message',$message);

            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'domains' . RDS . 'lists.html'); 
        }

        /**
         * @name delete
         * @description the delete action
         * @before init
         * @after setMenu,closeConnection
         */
        public function delete() 
        {
            $arguments = func_get_args();
            $id = isset($arguments) && count($arguments) > 0 ? $arguments[0] : null;

            $message = "Something went wrong !";
            $messageFlag = 'error';

            if(isset($id) && is_numeric($id))
            {
                # delete the server
                $domain = new Domain(array("id" => $id));
                $domain->delete();
                $message = "Record deleted succesfully !";
                $messageFlag = 'success';
            }

            # stores the message in the session 
            Session::set('proccess_message_flag',$messageFlag);
            Session::set('proccess_message',$message);

            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'domains' . RDS . 'lists.html');
        }
        
        /**
         * @name download
         * @description the download action
         * @before init
         * @after closeConnection
         */
        public function download()
        {
            # get the connected user
            $user = Session::get('mailtng_connected_user'); 
            $namecheap = new NameCheap();
            $domains = $namecheap->getAllDomains();
            
            $message = 'Internal Server Error !';
            $flag = 'error';

            if (count($domains))
            {
                foreach ($domains as $domain)
                {
                    $domainCheck = Domain::first(true, ['value = ?', trim($domain['Name'])]);

                    if (count($domainCheck) == 0)
                    {
                        # insert case
                        $domainObject = new Domain();
                        $domainObject->setStatus_id(1);
                        $domainObject->setValue($domain['Name']);
                        $domainObject->setIp_id(0);
                        $domainObject->setDomain_status("Available");
                        $domainObject->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                        $domainObject->setCreated_at(date("Y-m-d"));
                        $domainObject->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                        $domainObject->setLast_updated_at(date("Y-m-d"));
                        $domainObject->save();
                    }
                }

                $message = 'Records Stored Succesfully !';
                $flag = 'success';
            }

            # stores the message in the session 
            Session::set('proccess_message_flag',$flag);
            Session::set('proccess_message',$message);

            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'domains' . RDS . 'lists.html');
        }

        /**
         * @name setMenu
         * @description set the current menu to the template
         * @protected
         */
        public function setMenu() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_servers_management',true);
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