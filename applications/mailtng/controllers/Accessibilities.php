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
    use ma\applications\mailtng\models\admin\User as User;
    use ma\applications\mailtng\models\admin\ServerProvider as ServerProvider;
    use ma\applications\mailtng\models\admin\Server as Server;
    use ma\applications\mailtng\models\admin\Sponsor as Sponsor;
    use ma\applications\mailtng\models\admin\Offer as Offer;
    use ma\applications\mailtng\models\admin\Isp as Isp;
    use ma\applications\mailtng\models\data\DataList as DataList;
    use ma\mailtng\globals\Server as GloblServers;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\exceptions\types\PageException as PageException;
    
    /**
     * @name            Isps.controller 
     * @description     The Isps controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Accessibilities extends Controller 
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accessibilities' . RDS . 'servers.html');
        }
        
        /**
         * @name servers
         * @description manage servers accessibilities
         * @before init
         * @after setMenu,closeConnection
         */
        public function servers() 
        {
            # retrieve all form data
            $data = Request::getAllDataFromPOST();

            if(isset($data) && count($data))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                $userId = intval($data['user-id']);
                $providerId = intval($data['provider-id']);
                $authorisedServersIds = $data['authorized'];
                
                if($userId > 0)
                {
                    
                    $authorisedServersIds = strlen($authorisedServersIds) == 0 ? array() : explode(',', trim($authorisedServersIds,','));
                    
                    if(count($authorisedServersIds))
                    {
                        # get the server from the database
                        $authorisedServers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,authorized_users FROM admin.servers WHERE id IN ( " . implode(',',$authorisedServersIds). " )",true);
                        
                        # set authorized 
                        foreach ($authorisedServers as $server) 
                        {
                            $serverUsers = strlen($server['authorized_users']) > 0 && strpos($server['authorized_users'],',') > -1 ? array_unique(array_merge(array($userId),explode(',', trim($server['authorized_users'],',')))) : array($userId);
                            $serverUsers = (count($serverUsers) > 0) ? ',' . implode(',',$serverUsers) . ',' : '';
                            $serverUsers = trim($serverUsers,',') == '' ? '' : $serverUsers;
                            $serverObject = new Server(array('id' => $server['id']));
                            $serverObject->setAuthorized_users($serverUsers);
                            $serverObject->save();
                        }
                    }
                    
                    $condition = count($authorisedServersIds) ? "AND id NOT IN ( " . implode(',',$authorisedServersIds). " )" : "";
                    $notAuthorisedServers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,authorized_users FROM admin.servers WHERE provider_id = $providerId AND server_type_id > 1 $condition ",true);

                    # set not authorized 
                    foreach ($notAuthorisedServers as $server) 
                    {
                        $serverUsers = strlen($server['authorized_users']) > 0 && strpos($server['authorized_users'],',') > -1 ? str_replace(",$userId,",',',$server['authorized_users']) : $server['authorized_users'];
                        $serverUsers = trim($serverUsers,',') == '' ? '' : $serverUsers;
                        $serverObject = new Server(array('id' => $server['id']));
                        $serverObject->setAuthorized_users($serverUsers);
                        $serverObject->save();
                    }
                    
                    $message = "Accessibilities stored succesfully !";
                    $messageFlag = 'success';
                }

                # stores the message in the session 
                Session::set('proccess_message_flag',$messageFlag);
                Session::set('proccess_message',$message);
               
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accessibilities' . RDS . 'servers.html');
            }
            
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_accessibilities_servers',true);

            # get roles list 
            $users = User::all(true,array('application_role_id != ?',1),array('id','first_name','last_name'),'id','ASC');
            
            # get providers list 
            $providers = ServerProvider::all(true,array('status_id =  ?' , 1),array('id','name'),'id','ASC');

            # set the data into the template data system 
            $this->getPageView()->set('users',$users);
            $this->getPageView()->set('providers',$providers);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        } 
        
        /**
         * @name isps
         * @description manage isps accessibilities
         * @before init
         * @after setMenu,closeConnection
         */
        public function isps() 
        {
            # retrieve all form data
            $data = Request::getAllDataFromPOST();

            if(isset($data) && count($data))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                $userId = intval($data['user-id']);
                $authorisedIspsIds = $data['authorized'];
                
                if($userId > 0)
                {
                    
                    $authorisedIspsIds = strlen($authorisedIspsIds) == 0 ? array() : explode(',', trim($authorisedIspsIds,','));
                    
                    if(count($authorisedIspsIds))
                    {
                        # get the isp from the database
                        $authorisedServers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,authorized_users FROM admin.isps WHERE id IN ( " . implode(',',$authorisedIspsIds). " )",true);
                        
                        # set authorized 
                        foreach ($authorisedServers as $isp) 
                        {
                            $ispUsers = strlen($isp['authorized_users']) > 0 && strpos($isp['authorized_users'],',') > -1 ? array_unique(array_merge(array($userId),explode(',', trim($isp['authorized_users'],',')))) : array($userId);
                            $ispUsers = (count($ispUsers) > 0) ? ',' . implode(',',$ispUsers) . ',' : '';
                            $ispUsers = trim($ispUsers,',') == '' ? '' : $ispUsers;
                            $ispObject = new Isp(array('id' => $isp['id']));
                            $ispObject->setAuthorized_users($ispUsers);
                            $ispObject->save();
                        }
                    }
                    
                    $condition = count($authorisedIspsIds) ? "WHERE id NOT IN ( " . implode(',',$authorisedIspsIds). " )" : "";
                    $notAuthorisedIsps = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,authorized_users FROM admin.isps $condition ",true);

                    # set not authorized 
                    foreach ($notAuthorisedIsps as $isp) 
                    {
                        $ispUsers = strlen($isp['authorized_users']) > 0 && strpos($isp['authorized_users'],',') > -1 ? str_replace(",$userId,",',',$isp['authorized_users']) : $isp['authorized_users'];
                        $ispUsers = trim($ispUsers,',') == '' ? '' : $ispUsers;
                        $ispObject = new Isp(array('id' => $isp['id']));
                        $ispObject->setAuthorized_users($ispUsers);
                        $ispObject->save();
                    }
                    
                    $message = "Accessibilities stored succesfully !";
                    $messageFlag = 'success';
                }

                # stores the message in the session 
                Session::set('proccess_message_flag',$messageFlag);
                Session::set('proccess_message',$message);
               
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accessibilities' . RDS . 'isps.html');
            }
            
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_accessibilities_isps',true);

            # get roles list 
            $users = User::all(true,array('application_role_id != ?',1),array('id','first_name','last_name'),'id','ASC');

            # set the data into the template data system 
            $this->getPageView()->set('users',$users);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        } 

        /**
         * @name offers
         * @description manage offers accessibilities
         * @before init
         * @after setMenu,closeConnection
         */
        public function offers() 
        {
            # retrieve all form data
            $data = Request::getAllDataFromPOST();

            if(isset($data) && count($data))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                $userId = intval($data['user-id']);
                $sponsorId = intval($data['sponsor-id']);
                $authorisedOffersIds = $data['authorized'];
                
                if($userId > 0)
                {
                    $authorisedOffersIds = strlen($authorisedOffersIds) == 0 ? array() : explode(',', trim($authorisedOffersIds,','));
                    
                    if(count($authorisedOffersIds))
                    {
                        # get the offer from the database
                        $authorisedOffers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,authorized_users FROM admin.offers WHERE id IN ( " . implode(',',$authorisedOffersIds). " )",true);
                        
                        # set authorized 
                        foreach ($authorisedOffers as $offer) 
                        {
                            $offerUsers = strlen($offer['authorized_users']) > 0 && strpos($offer['authorized_users'],',') > -1 ? array_unique(array_merge(array($userId),explode(',', trim($offer['authorized_users'],',')))) : array($userId);
                            $offerUsers = (count($offerUsers) > 0) ? ',' . implode(',',$offerUsers) . ',' : '';
                            $offerUsers = trim($offerUsers,',') == '' ? '' : $offerUsers;
                            $offerObject = new Offer(array('id' => $offer['id']));
                            $offerObject->setAuthorized_users($offerUsers);
                            $offerObject->save();
                        }
                    }
                    
                    $condition = count($authorisedOffersIds) ? "AND id NOT IN ( " . implode(',',$authorisedOffersIds). " )" : "";
                    $notAuthorisedOffers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,authorized_users FROM admin.offers WHERE sponsor_id = $sponsorId $condition ",true);

                    # set not authorized 
                    foreach ($notAuthorisedOffers as $offer) 
                    {
                        $offerUsers = strlen($offer['authorized_users']) > 0 && strpos($offer['authorized_users'],',') > -1 ? str_replace(",$userId,",',',$offer['authorized_users']) : $offer['authorized_users'];
                        $offerUsers = trim($offerUsers,',') == '' ? '' : $offerUsers;
                        $offerObject = new Offer(array('id' => $offer['id']));
                        $offerObject->setAuthorized_users($offerUsers);
                        $offerObject->save();
                    }
                    
                    $message = "Accessibilities stored succesfully !";
                    $messageFlag = 'success';
                }

                # stores the message in the session 
                Session::set('proccess_message_flag',$messageFlag);
                Session::set('proccess_message',$message);
               
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accessibilities' . RDS . 'offers.html');
            }
            
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_accessibilities_offers',true);

            # get roles list 
            $users = User::all(true,array('application_role_id != ?',1),array('id','first_name','last_name'),'id','ASC');
            
            # get sponsors list 
            $sponsors = Sponsor::all(true,array('status_id =  ?' , 1),array('id','name'),'id','ASC');

            # set the data into the template data system 
            $this->getPageView()->set('users',$users);
            $this->getPageView()->set('sponsors',$sponsors);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        } 
        
        /**
         * @name lists
         * @description manage lists accessibilities
         * @before init
         * @after setMenu,closeConnection
         */
        public function lists() 
        {
            # retrieve all form data
            $data = Request::getAllDataFromPOST();

            if(isset($data) && count($data))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                $userId = intval($data['user-id']);
                $ispId = intval($data['isp-id']);
                $authorisedListsIds = $data['authorized'];
                
                if($userId > 0)
                {
                    $authorisedListsIds = strlen($authorisedListsIds) == 0 ? array() : explode(',', trim($authorisedListsIds,','));
                    
                    if(count($authorisedListsIds))
                    {
                        # get the server from the database
                        $authorisedLists = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,authorized_users FROM admin.data_lists WHERE id IN ( " . implode(',',$authorisedListsIds). " )",true);
                        
                        # set authorized 
                        foreach ($authorisedLists as $server) 
                        {
                            $listUsers = strlen($server['authorized_users']) > 0 && strpos($server['authorized_users'],',') > -1 ? array_unique(array_merge(array($userId),explode(',', trim($server['authorized_users'],',')))) : array($userId);
                            $listUsers = (count($listUsers) > 0) ? ',' . implode(',',$listUsers) . ',' : '';
                            $listUsers = trim($listUsers,',') == '' ? '' : $listUsers;
                            $serverObject = new DataList(array('id' => $server['id']));
                            $serverObject->setAuthorized_users($listUsers);
                            $serverObject->save();
                        }
                    }
                    
                    $condition = count($authorisedListsIds) ? "AND id NOT IN ( " . implode(',',$authorisedListsIds). " )" : "";
                    $notAuthorisedLists = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,authorized_users FROM admin.data_lists WHERE isp_id = $ispId $condition ",true);

                    # set not authorized 
                    foreach ($notAuthorisedLists as $server) 
                    {
                        $listUsers = strlen($server['authorized_users']) > 0 && strpos($server['authorized_users'],',') > -1 ? str_replace(",$userId,",',',$server['authorized_users']) : $server['authorized_users'];
                        $listUsers = trim($listUsers,',') == '' ? '' : $listUsers;
                        $serverObject = new DataList(array('id' => $server['id']));
                        $serverObject->setAuthorized_users($listUsers);
                        $serverObject->save();
                    }
                    
                    $message = "Accessibilities stored succesfully !";
                    $messageFlag = 'success';
                }

                # stores the message in the session 
                Session::set('proccess_message_flag',$messageFlag);
                Session::set('proccess_message',$message);
               
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accessibilities' . RDS . 'lists.html');
            }
            
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_accessibilities_lists',true);

            # get roles list 
            $users = User::all(true,array('application_role_id != ?',1),array('id','first_name','last_name'),'id','ASC');
            
            # get isps list 
            $isps = Isp::all(true,array('status_id =  ?' , 1),array('id','name'),'id','ASC');

            # set the data into the template data system 
            $this->getPageView()->set('users',$users);
            $this->getPageView()->set('isps',$isps);
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }
        
        /**
         * @name getServers
         * @description gets the servers by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $userId = isset($arguments) && count($arguments) ? intval($arguments[0]) : null;
                $providerId = isset($arguments) && count($arguments) ? intval($arguments[1]) : null;

                if(isset($userId) && isset($providerId) && $userId > 0 && $providerId > 0 && $this->getDefaultContentType() == 'text/json')
                {
                    # get the server from the database
                    $authorisedServers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.servers WHERE status_id = 1 AND provider_id = $providerId AND server_type_id > 1 AND authorized_users LIKE '%,$userId,%'",true);
                    $notAuthorisedServers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.servers WHERE status_id = 1 AND provider_id = $providerId AND server_type_id > 1 AND ( authorized_users = '' OR authorized_users IS NULL OR authorized_users NOT LIKE '%,$userId,%' )",true);

                    die(json_encode(array( "authorised" => $authorisedServers , "notAuthorised" => $notAuthorisedServers)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accessibilities' . RDS . 'servers.html');
            }
        }
        
        /**
         * @name getIsps
         * @description gets the isps by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getIsps() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $userId = isset($arguments) && count($arguments) ? intval($arguments[0]) : null;

                if(isset($userId) && $userId > 0 && $this->getDefaultContentType() == 'text/json')
                {
                    # get the server from the database
                    $authorisedIsps = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.isps WHERE status_id = 1 AND authorized_users LIKE '%,$userId,%'",true);
                    $notAuthorisedIsps = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.isps WHERE status_id = 1 AND authorized_users = '' OR authorized_users IS NULL OR authorized_users NOT LIKE '%,$userId,%'",true);

                    die(json_encode(array( "authorised" => $authorisedIsps , "notAuthorised" => $notAuthorisedIsps)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accessibilities' . RDS . 'isps.html');
            }
        }
        
        /**
         * @name getOffers
         * @description gets the offers by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getOffers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $userId = isset($arguments) && count($arguments) ? intval($arguments[0]) : null;
                $sponsorId = isset($arguments) && count($arguments) ? intval($arguments[1]) : null;

                if(isset($userId) && isset($sponsorId) && $userId > 0 && $sponsorId > 0 && $this->getDefaultContentType() == 'text/json')
                {
                    # get the server from the database
                    $authorisedServers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.offers WHERE status_id = 1 AND sponsor_id = $sponsorId AND authorized_users LIKE '%,$userId,%'",true);
                    $notAuthorisedServers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.offers WHERE status_id = 1 AND sponsor_id = $sponsorId AND ( authorized_users = '' OR authorized_users IS NULL OR authorized_users NOT LIKE '%,$userId,%' )",true);

                    die(json_encode(array( "authorised" => $authorisedServers , "notAuthorised" => $notAuthorisedServers)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accessibilities' . RDS . 'servers.html');
            }
        }
        
        /**
         * @name getLists
         * @description gets the lists by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getLists() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $userId = isset($arguments) && count($arguments) ? intval($arguments[0]) : null;
                $ispId = isset($arguments) && count($arguments) ? intval($arguments[1]) : null;

                if(isset($userId) && isset($ispId) && $userId > 0 && $ispId > 0 && $this->getDefaultContentType() == 'text/json')
                {
                    # get the server from the database
                    $authorisedLists = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.data_lists WHERE isp_id = $ispId AND authorized_users LIKE '%,$userId,%'",true);
                    $notAuthorisedLists = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.data_lists WHERE isp_id = $ispId AND  authorized_users = '' OR authorized_users IS NULL OR authorized_users NOT LIKE '%,$userId,%' ",true);
                    
                    die(json_encode(array( "authorised" => $authorisedLists , "notAuthorised" => $notAuthorisedLists)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accessibilities' . RDS . 'lists.html');
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
            $this->getMasterView()->set('menu_admin_users_management',true);
            $this->getMasterView()->set('menu_admin_accessibilities',true);
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