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
    use ma\applications\mailtng\models\admin\Isp as Isp;
    use ma\applications\mailtng\models\admin\Status as Status;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\exceptions\types\PageException as PageException;
    /**
     * @name            Isps.controller 
     * @description     The Isps controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Isps extends Controller 
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'isps' . RDS . 'lists.html');
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
            $this->getMasterView()->set('menu_admin_isps',true);
            $this->getMasterView()->set('menu_admin_isps_lists',true);

            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT i.id AS id,"
                                            . " i.name AS isp_name,"
                                            . " s.name AS status," 
                                            . " uc.username AS created_by,"
                                            . " i.created_at AS created_date,"
                                            . " up.username AS last_updated_by,"
                                            . " i.last_updated_at AS last_updated_at"
                                            . " FROM admin.isps i"
                                            . " LEFT JOIN admin.users uc ON uc.id = i.created_by"
                                            . " LEFT JOIN admin.status s ON s.id = i.status_id"
                                            . " LEFT JOIN admin.users up ON up.id = i.last_updated_by"
                                            . " ORDER BY i.id", true);
                                    
            # get all the columns names 
            $columns = array('id','isp_name','status','created_by','created_date','last_updated_by','last_updated_at');

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
            $this->getMasterView()->set('menu_admin_isps',true);
            $this->getMasterView()->set('menu_admin_isps_add',true);
            
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
            $this->getMasterView()->set('menu_admin_isps',true);
            $this->getMasterView()->set('menu_admin_isps_add',true);
            
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
            
            # retrieves the data from post
            $id = Request::getParameterFromPOST('isp-id');
            $ispStatus = Request::getParameterFromPOST('status-id');
            $IspName = Request::getParameterFromPOST('isp-name');
            
            if(isset($IspName))
            {
                $message = "Something went wrong !";
                $messageFlag = 'danger';
                
                if($id != NULL && is_numeric($id))
                {
                    # update case
                    $isp = new Isp(array("id" => $id));
                    $isp->setStatus_id(intval($ispStatus));
                    $isp->setName($IspName);
                    $isp->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                    $isp->setLast_updated_at(date("Y-m-d"));

                    $result = $isp->save(); 

                    if($result > -1)
                    {
                        $message = "Record updated succesfully !";
                        $messageFlag = 'success';
                    }
                }
                else
                {
                    # insert case
                    $isp = new Isp();
                    $isp->setStatus_id(intval($ispStatus));
                    $isp->setName($IspName);
                    $isp->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                    $isp->setCreated_at(date("Y-m-d"));
                    $isp->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                    $isp->setLast_updated_at(date("Y-m-d"));

                    $result = $isp->save();  

                    if($result > -1)
                    {
                        # switch to lists database
                        Database::switchToDatabase('lists');
                        
                        # create database scheme for this isp 
                        $isp = strtolower(trim($IspName));
                        
                        # create Scheme 
                        Database::getCurrentDatabaseConnector()->executeQuery("CREATE SCHEMA IF NOT EXISTS $isp");

                        $message = "Record stored succesfully !";
                        $messageFlag = 'success';
                    }
               }

               # stores the message in the session 
               Session::set('proccess_message_flag',$messageFlag);
               Session::set('proccess_message',$message);
            }
            
            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'isps' . RDS . 'lists.html'); 
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
            $messageFlag = 'danger';

            if(isset($id) && is_numeric($id))
            {
                # delete the server
                $isp = new Isp(array("id" => $id));
                $isp->delete();
                $message = "Record deleted successfully !";
                $messageFlag = 'success';
            }

            # stores the message in the session 
            Session::set('proccess_message_flag',$messageFlag);
            Session::set('proccess_message',$message);

            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'isps' . RDS . 'lists.html');
        }
        
        /**
         * @name setMenu
         * @description set the current menu to the template
         * @protected
         */
        public function setMenu() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_data',true);
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