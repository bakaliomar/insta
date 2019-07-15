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
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\http\Response as Response;
    use ma\mailtng\www\URL as URL;
    use ma\applications\mailtng\models\admin\User as User;
    /**
     * @name            Authentication.controller 
     * @description     The Authentication controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Authentication extends Controller 
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
        }
        
        /**
         * @before init
         * @after closeConnection
         */
        public function login() 
        {
            # check authentication
            $user = Session::get('mailtng_connected_user');  
            
            if(isset($user))
            {
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'home.html');
            }

            $arguments = func_get_args(); 
            $action = isset($arguments) && count($arguments) ? $arguments[0] : null;
            
            switch ($action)
            {
                case 'authenticate' : 
                {
                    $username = Request::getParameterFromPOST('username');
                    $password = Request::getParameterFromPOST('password');
                    $remember = Request::getParameterFromPOST('remember-me'); 
            
                    $this->authenticate($username,$password,$remember);
                    break;
                }
                default :
                {
                    $this->setShowMasterView(false);
                
                    # check if there is a message from a previous action
                    $message = Session::getThenDel('login_error_message');

                    if(isset($message))
                    {
                        # set the message into the template data system 
                        $this->getPageView()->set('login_error_message',$message);
                    }
                    break;
                }
            }
        }

        /**
         * @before init
         * @after closeConnection
         */
        public function logout() 
        {
            $this->setShowMasterView(false);
            $this->setShowPageView(false);
            Session::destroy();
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'authentication' . RDS . 'login.html');
        }

        /**
         * @name setMenu
         * @description set the current menu to the template
         * @protected
         */
        public function setMenu() 
        {}
        
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
        
        /**
         * @name authenticate
         * @description takes care of the login process 
         * @once
         * @protected
         */
        public function authenticate($username,$password,$remember = false) 
        {
            $this->setShowMasterView(false);
            $this->setShowPageView(false);
            
            $user = User::first(true,array("username = ? AND password = ? ",array($username,md5($password))),array('id','application_role_id','first_name','last_name','telephone','email','username'));

            if(isset($user))
            {
                # remember me case
                if($remember)
                {}

                Session::set('mailtng_connected_user',$user);
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'home.html');
            }  
            else
            {
                # stores the message in the session 
                Session::set('login_error_message',"Incorrect username or password !");
                
                # stores the message in the session 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'authentication' . RDS . 'login.html');
            } 
        }

    }
}