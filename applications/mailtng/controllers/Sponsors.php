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
    use ma\applications\mailtng\models\admin\Sponsor as Sponsor;
    use ma\applications\mailtng\models\admin\Status as Status;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\exceptions\types\PageException as PageException;
    /**
     * @name            Sponsors.controller 
     * @description     The Sponsors controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Sponsors extends Controller 
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'sponsors' . RDS . 'lists.html');
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
            $this->getMasterView()->set('menu_admin_sponsors',true);
            $this->getMasterView()->set('menu_admin_sponsors_lists',true);

            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT sp.id AS id,"
                                            . " sp.name AS sponsor_name,"
                                            . " sp.affiliate_id AS affiliate_id,"
                                            . " sp.website AS sponsor_website,"
                                            . " s.name AS status," 
                                            . " uc.username AS created_by,"
                                            . " sp.created_at AS created_date,"
                                            . " up.username AS last_updated_by,"
                                            . " sp.last_updated_at AS last_updated_at"
                                            . " FROM admin.sponsors sp"
                                            . " LEFT JOIN admin.users uc ON uc.id = sp.created_by"
                                            . " LEFT JOIN admin.status s ON s.id = sp.status_id"
                                            . " LEFT JOIN admin.users up ON up.id = sp.last_updated_by"
                                            . " ORDER BY sp.id", true);
                                    
            # get all the columns names 
            $columns = array('id','sponsor_name','affiliate_id','sponsor_website','status','created_by','created_date','last_updated_by','last_updated_at');

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
            $this->getMasterView()->set('menu_admin_sponsors',true);
            $this->getMasterView()->set('menu_admin_sponsors_add',true);
            
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
            $this->getMasterView()->set('menu_admin_sponsors',true);
            $this->getMasterView()->set('menu_admin_sponsors_add',true);
            
            $arguments = func_get_args(); 
            $id = isset($arguments) && count($arguments) ? $arguments[0] : null;

            if(isset($id) && is_numeric($id))
            {
                # retrieve the server by id
                $sponsor = Sponsor::first(true,array('id = ?',$id));
                $status = Status::all(true);

                # set the data to the template
                $this->getPageView()->set('sponsor',$sponsor);
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
            
            # retrieves the data from post
            $id = Request::getParameterFromPOST('sponsor-id');
            $affiliateId = Request::getParameterFromPOST('affiliate-id');
            $status = Request::getParameterFromPOST('status-id');
            $sponsorName = Request::getParameterFromPOST('sponsor-name');
            $sponsorWebsite = Request::getParameterFromPOST('sponsor-website');
            $sponsorUsername = Request::getParameterFromPOST('sponsor-username');
            $sponsorPassword = Request::getParameterFromPOST('sponsor-password');
            $sponsorApiUrl = Request::getParameterFromPOST('sponsor-api-url');
            $sponsorApiKey = Request::getParameterFromPOST('sponsor-api-key');
            $sponsorApiType = Request::getParameterFromPOST('sponsor-api-type');
            
            if(isset($sponsorName))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                if($id != NULL && is_numeric($id))
                {
                    # update case
                    $sponsor = new Sponsor(array("id" => $id));
                    $sponsor->setStatus_id(intval($status));
                    $sponsor->setAffiliate_id(intval($affiliateId));
                    $sponsor->setName($sponsorName);
                    $sponsor->setWebsite($sponsorWebsite);
                    $sponsor->setUsername($sponsorUsername);
                    $sponsor->setPassword($sponsorPassword);
                    $sponsor->setApi_url($sponsorApiUrl);
                    $sponsor->setApi_key($sponsorApiKey);
                    $sponsor->setApi_type($sponsorApiType);
                    $sponsor->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                    $sponsor->setLast_updated_at(date("Y-m-d"));

                    $result = $sponsor->save(); 

                    if($result > -1)
                    {
                        $message = "Record updated succesfully !";
                        $messageFlag = 'success';
                    }
                }
                else
                {
                    # insert case
                    $sponsor = new Sponsor();
                    $sponsor->setStatus_id(intval($status));
                    $sponsor->setAffiliate_id(intval($affiliateId));
                    $sponsor->setName($sponsorName);
                    $sponsor->setWebsite($sponsorWebsite);
                    $sponsor->setUsername($sponsorUsername);
                    $sponsor->setPassword($sponsorPassword);
                    $sponsor->setApi_url($sponsorApiUrl);
                    $sponsor->setApi_key($sponsorApiKey);
                    $sponsor->setApi_type($sponsorApiType);
                    $sponsor->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                    $sponsor->setCreated_at(date("Y-m-d"));
                    $sponsor->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                    $sponsor->setLast_updated_at(date("Y-m-d"));

                    $result = $sponsor->save();  

                    if($result > -1)
                    {
                        $message = "Record stored succesfully !";
                        $messageFlag = 'success';
                    }
               }

               # stores the message in the session 
               Session::set('proccess_message_flag',$messageFlag);
               Session::set('proccess_message',$message);
            }
            
            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'sponsors' . RDS . 'lists.html'); 
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
                $sponsor = new Sponsor(array("id" => $id));
                $sponsor->delete();
                $message = "Record deleted successfully !";
                $messageFlag = 'success';
            }

            # stores the message in the session 
            Session::set('proccess_message_flag',$messageFlag);
            Session::set('proccess_message',$message);

            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'sponsors' . RDS . 'lists.html');
        }
        
        /**
         * @name setMenu
         * @description set the current menu to the template
         * @protected
         */
        public function setMenu() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_mailing_manage',true);
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