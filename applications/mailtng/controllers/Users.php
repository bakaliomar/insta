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
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\http\Response as Response;
    use ma\mailtng\www\URL as URL;
    use ma\mailtng\types\Arrays as Arrays;
    use ma\applications\mailtng\models\admin\User as User;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\applications\mailtng\models\admin\Status as Status;
    use ma\applications\mailtng\models\admin\ApplicationRoles as ApplicationRoles;
    use ma\mailtng\exceptions\types\PageException as PageException;
    /**
     * @name            Users.controller 
     * @description     The Users controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Users extends Controller 
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'users' . RDS . 'lists.html');
        }

        /**
         * @name lists
         * @description the lists action
         * @before init
         * @after setMenu,closeConnection
         */
        public function lists() 
        {
            # check authorization access
            if(!in_array(Arrays::getElement(Session::get('mailtng_connected_user'),'application_role_id'),array(1)))
            {
                throw new PageException("403 Access Denied",403);
            }
            
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_users',true);
            $this->getMasterView()->set('menu_admin_users_list',true);

            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT u.id AS id,"
                                            . " r.name AS role,"
                                            . " u.first_name AS first_name,"
                                            . " u.last_name AS last_name,"
                                            . " u.telephone AS telephone,"
                                            . " u.email AS email,"
                                            . " st.name AS status,"
                                            . " uc.username AS created_by,"
                                            . " st.created_at AS created_date,"
                                            . " up.username AS last_updated_by,"
                                            . " st.last_updated_at AS last_updated_at"
                                            . " FROM admin.users u"
                                            . " LEFT JOIN admin.application_roles r ON r.id = u.application_role_id"
                                            . " LEFT JOIN admin.users uc ON uc.id = u.created_by"
                                            . " LEFT JOIN admin.status st ON st.id = u.status_id"
                                            . " LEFT JOIN admin.users up ON up.id = u.last_updated_by"
                                            . " ORDER BY u.id", true);
                                    
            # get all the columns names 
            $columns = array('id','role','first_name','last_name','telephone','email','status','created_by','created_date','last_updated_by','last_updated_at');

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
            # check authorization access
            if(!in_array(Arrays::getElement(Session::get('mailtng_connected_user'),'application_role_id'),array(1)))
            {
                throw new PageException("403 Access Denied",403);
            }
            
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_users',true);
            $this->getMasterView()->set('menu_admin_users_add',true);
            
            # get status list 
            $status = Status::all(true,array(),array('id','name'),'id','ASC');
            
            # get roles list 
            $roles = ApplicationRoles::all(true,array(),array('id','name'),'id','ASC');

            # set the list into the template data system 
            $this->getPageView()->set('status',$status);
            $this->getPageView()->set('roles',$roles);
        }
        
        /**
         * @name add
         * @description the add action
         * @before init
         * @after setMenu,closeConnection
         */
        public function edit() 
        {
            $arguments = func_get_args(); 
            $id = isset($arguments) && count($arguments) ? $arguments[0] : null;

            # check authorization access
            if(!in_array(Arrays::getElement(Session::get('mailtng_connected_user'),'application_role_id'),array(1)) && Arrays::getElement(Session::get('mailtng_connected_user'),'id') != $id)
            {
                throw new PageException("403 Access Denied",403);
            }
            
            if(isset($id) && is_numeric($id))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_users',true);
                $this->getMasterView()->set('menu_admin_users_add',true);
            
                # retrieve the server by id
                $user = User::first(true,array('id = ?',$id));
                
                # get status list 
                $status = Status::all(true,array(),array('id','name'),'id','ASC');

                # get roles list 
                $roles = ApplicationRoles::all(true,array(),array('id','name'),'id','ASC');

                # set the list into the template data system
                $this->getPageView()->set('user',$user);
                $this->getPageView()->set('status',$status);
                $this->getPageView()->set('roles',$roles);
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
            $id = Request::getParameterFromPOST('user-id');
            
            # check authorization access
            if(!in_array(Arrays::getElement(Session::get('mailtng_connected_user'),'application_role_id'),array(1)) && Arrays::getElement(Session::get('mailtng_connected_user'),'id') != $id)
            {
                throw new PageException("403 Access Denied",403);
            }
            
            $status = Request::getParameterFromPOST('status-id');
            $role = Request::getParameterFromPOST('role-id');
            $firstName = Request::getParameterFromPOST('first-name');
            $lastName = Request::getParameterFromPOST('last-name');
            $telephone = Request::getParameterFromPOST('telephone');
            $email = Request::getParameterFromPOST('email');
            $username = Request::getParameterFromPOST('username');
            $password = Request::getParameterFromPOST('password');
            
            if(isset($username))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                if($id != NULL && is_numeric($id))
                {
                    # update case
                    $newUser = new User(array("id" => $id));
                    $newUser->setStatus_id(intval($status));
                    $newUser->setApplication_role_id(intval($role));
                    $newUser->setFirst_name($firstName);
                    $newUser->setLast_name($lastName);
                    $newUser->setTelephone($telephone);
                    $newUser->setEmail($email);
                    $newUser->setUsername($username);
                    
                    if(strlen($password) > 0)
                    {
                        $newUser->setPassword(md5($password));
                    }

                    $newUser->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                    $newUser->setLast_updated_at(date("Y-m-d"));

                    $result = $newUser->save(); 

                    if($result > -1)
                    {
                        $message = "Record updated succesfully !";
                        $messageFlag = 'success';
                    }
                }
                else
                {
                    # insert case
                    $newUser = new User();              
                    $newUser->setStatus_id(intval($status));
                    $newUser->setApplication_role_id(intval($role));
                    $newUser->setFirst_name($firstName);
                    $newUser->setLast_name($lastName);
                    $newUser->setTelephone($telephone);
                    $newUser->setEmail($email);
                    $newUser->setUsername($username);
                    $newUser->setPassword(md5($password));
                    $newUser->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                    $newUser->setCreated_at(date("Y-m-d"));
                    $newUser->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                    $newUser->setLast_updated_at(date("Y-m-d"));

                    $result = $newUser->save();  

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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'users' . RDS . 'lists.html'); 
        }
        
        /**
         * @name delete
         * @description the delete action
         * @before init
         * @after setMenu,closeConnection
         */
        public function delete() 
        {
            # check authorization access
            if(!in_array(Arrays::getElement(Session::get('mailtng_connected_user'),'application_role_id'),array(1)))
            {
                throw new PageException("403 Access Denied",403);
            }
            
            $arguments = func_get_args();
            $id = isset($arguments) && count($arguments) > 0 ? $arguments[0] : null;

            $message = "Something went wrong !";
            $messageFlag = 'error';

            if(isset($id) && is_numeric($id))
            {
                # delete the server
                $user = new User(array("id" => $id));
                $user->delete();
                $message = "Record deleted successfully !";
                $messageFlag = 'success';
            }

            # stores the message in the session 
            Session::set('proccess_message_flag',$messageFlag);
            Session::set('proccess_message',$message);

            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'users' . RDS . 'lists.html');
        }
        
        /**
         * @name roles
         * @description the roles action
         * @before init
         * @after setMenu,closeConnection
         */
        public function roles() 
        {
            # check authorization access
            if(!in_array(Arrays::getElement(Session::get('mailtng_connected_user'),'application_role_id'),array(1)))
            {
                throw new PageException("403 Access Denied",403);
            }
            
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';

            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_users_roles',true);
                
                switch ($page) 
                {
                    case 'lists' :
                    {
                        # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'users' . DS . 'roles' . DS . 'lists' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_admin_users_roles_list',true);

                        # get the data from the database
                        $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT r.id AS id,"
                                                        . " r.name AS role_name,"
                                                        . " s.name AS status," 
                                                        . " uc.username AS created_by,"
                                                        . " r.created_at AS created_date,"
                                                        . " up.username AS last_updated_by,"
                                                        . " r.last_updated_at AS last_updated_at"
                                                        . " FROM admin.application_roles r"
                                                        . " LEFT JOIN admin.users uc ON uc.id = r.created_by"
                                                        . " LEFT JOIN admin.status s ON s.id = r.status_id"
                                                        . " LEFT JOIN admin.users up ON up.id = r.last_updated_by"
                                                        . " ORDER BY r.id", true);

                        # get all the columns names 
                        $columns = array('id','role_name','status','created_by','created_date','last_updated_by','last_updated_at');

                        # set the list into the template data system 
                        $this->getPageView()->set('list',$list);

                        # set the columns list into the template data system 
                        $this->getPageView()->set('columns',$columns);

                        # check for message 
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'add' :
                    {
                        # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'users' . DS . 'roles' . DS . 'add' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_admin_users_roles_add',true);
                        
                        # get status list 
                        $status = Status::all(true,array(),array('id','name'),'id','ASC');
                        
                        # set the list into the template data system 
                        $this->getPageView()->set('status',$status);
                        break;
                    }
                    case 'edit' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        if(isset($id) && is_numeric($id))
                        {
                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'users' . DS . 'roles' . DS . 'edit' . '.' . $this->getDefaultExtension());
                            
                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_users_roles_add',true);
                            
                            # retrieve the server by id
                            $role = ApplicationRoles::first(true,array('id = ?',$id));
                            
                            # get status list 
                            $status = Status::all(true,array(),array('id','name'),'id','ASC');

                            # set the data to the template
                            $this->getPageView()->set('role',$role);

                            # set the list into the template data system 
                            $this->getPageView()->set('status',$status);
                        }
                        break;
                    }
                    case 'delete' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        $message = "Something went wrong !";
                        $messageFlag = 'error';

                        if(isset($id) && is_numeric($id))
                        {
                            # delete the server
                            $role = new ApplicationRoles(array("id" => $id));
                            $role->delete();
                            $message = "Record deleted successfully !";
                            $messageFlag = 'success';
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);

                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'users' . RDS . 'roles' . RDS . 'lists.html');
                        
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('mailtng_connected_user'); 

                        # retrieves the data from post
                        $id = Request::getParameterFromPOST('role-id');
                        $roleStatus = Request::getParameterFromPOST('status-id');
                        $roleName = Request::getParameterFromPOST('role-name');
                        
                        if(isset($roleName))
                        {
                            $message = "Something went wrong !";
                            $messageFlag = 'error';

                            if($id != NULL && is_numeric($id))
                            {
                                # update case
                                $role = new ApplicationRoles(array("id" => $id));
                                $role->setStatus_id(intval($roleStatus));
                                $role->setName($roleName);
                                $role->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $role->setLast_updated_at(date("Y-m-d"));

                                $result = $role->save(); 

                                if($result > -1)
                                {
                                    $message = "Record updated succesfully !";
                                    $messageFlag = 'success';
                                }
                            }
                            else
                            {
                                # insert case
                                $role = new ApplicationRoles();
                                $role->setStatus_id(intval($roleStatus));
                                $role->setName($roleName);
                                $role->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                $role->setCreated_at(date("Y-m-d"));
                                $role->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $role->setLast_updated_at(date("Y-m-d"));

                                $result = $role->save(); 

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
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'users' . RDS . 'roles' . RDS . 'lists.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'users' . RDS . 'roles' . RDS . 'lists.html');
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
            $this->getMasterView()->set('menu_admin_users_management',true);
        }
        
        /**
         * @name closeConnection
         * @description makes sure to close all open connections after execution finished
         * @once
         * @protected
         */
        public function closeConnection() 
        {
            # disconnect from all databases 
            Database::secureDisconnect();
        }
    }
}