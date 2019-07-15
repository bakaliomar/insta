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
    use ma\mailtng\application\Application as Application;
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\www\URL as URL;  
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\configuration\Configuration as Configuration;
    use ma\mailtng\globals\Server as GloblServers;
    use ma\mailtng\http\Request as Request;
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\files\Paths as Paths;
    use ma\mailtng\os\System as System;
    use ma\mailtng\encryption\Crypto as Crypto;
    use ma\applications\mailtng\models\admin\Isp as Isp;
    use ma\applications\mailtng\models\admin\Domain as Domain;
    use ma\applications\mailtng\models\admin\Server as Server;
    use ma\applications\mailtng\models\admin\Sponsor as Sponsor;
    use ma\applications\mailtng\models\admin\Offer as Offer;
    use ma\applications\mailtng\models\admin\OfferFromName as OfferName;
    use ma\applications\mailtng\models\admin\OfferSubject as OfferSubject;
    use ma\applications\mailtng\models\admin\OfferCreative as OfferCreative;
    use ma\applications\mailtng\models\admin\OfferLink as OfferLink;
    use ma\applications\mailtng\models\admin\Vertical as Vertical;
    use ma\applications\mailtng\models\admin\Header as Header;
    use ma\applications\mailtng\models\admin\ServerProvider as ServerProvider;
    use ma\applications\mailtng\models\data\DataType as DataType;
    use ma\applications\mailtng\models\production\Drop as Drop;
    use ma\applications\mailtng\models\admin\Ip as Ip;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\exceptions\types\PageException as PageException;
    /**
     * @name            Mail.controller 
     * @description     The Mail controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Mail extends Controller 
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
            # get the connected user
            $user = Session::get('mailtng_connected_user'); 
                
            $arguments = func_get_args();          
            $dropId = isset($arguments) && count($arguments) ? $arguments[0] : null;

            if(isset($dropId) && is_numeric($dropId))
            {
                # get the drop from the database
                $drop = Drop::first(true,array('id = ?',$dropId));
                $this->getPageView()->set('drop',$drop);
            }
                
            # get all servers from the database
            $serverProviders = ServerProvider::all(true,array('status_id = ? ',1),array('id','name'));
            
            if(!in_array($user['application_role_id'],[1]))
            {
                $servers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,provider_id FROM admin.servers WHERE server_type_id > 1 AND authorized_users LIKE '%,{$user['id']},%'",true);
            }
            else
            {
                $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','provider_id'));
            }
            
            # check for providers that has no servers in production 
            foreach ($serverProviders as $index => $provider) 
            {
                $count = 0;
                
                foreach ($servers as $server) 
                {
                    if($server['provider_id'] == $provider['id'])
                    {
                        $count++;
                    }
                }
                
                if($count == 0)
                {
                    unset($serverProviders[$index]);
                }
            }
            
            # get all sponsors from the database
            $sponsors = Sponsor::all(true,array('status_id = ? ',1));
            
            # get all sponsors from the database
            $verticals = Vertical::all(true,array('status_id = ? ',1));
            
            # get all providers from the database
            $dataTypes = DataType::all(true,array('status_id = ? ',1),array('*'),'id','ASC');

            # get all isps from the database
            if(!in_array($user['application_role_id'],[1]))
            {
                $isps = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.isps WHERE status_id = 1 AND authorized_users LIKE '%,{$user['id']},%'",true);
            }
            else
            {
                $isps = Isp::all(true,array('status_id = ? ',1));
            }
 
            # get all domains from the database
            $domains = Domain::all(true,array('status_id = ? AND domain_status = ?',array(1,'Redirect')));
      
            # get the header template 
            $header = '';
            $file = APPS_FOLDER . DS . Application::getPrefix() . DS . DEFAULT_ASSETS_DIRECTORY . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'interface' . DS . 'header.tpl';

            if(file_exists($file))
            {
                $header = file_get_contents($file);
            }
            
            $headers = Header::all(true,array('user_id = ?',intval(Arrays::getElement($user,'id'))));
                
            # get the header template 
            $help = '';
            $file = APPS_FOLDER . DS . Application::getPrefix() . DS . DEFAULT_ASSETS_DIRECTORY . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'interface' . DS . 'help.tpl';

            if(file_exists($file))
            {
                $help = file_get_contents($file);
            }

            # get pmta port
            $configuration = new Configuration(array( "type" => "ini" ));
            $result = $configuration->initialize()->parse(Paths::getCurrentApplicationRealPath() . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'pmta',false);
            $pmtaPort = (count($result) > 0 && key_exists('pmta_http_port',$result)) ? $result['pmta_http_port'] : 8080;

            # set the data into the template data system 
            $this->getPageView()->set('servers',$servers);
            $this->getPageView()->set('serverProviders',$serverProviders);
            $this->getPageView()->set('isps',$isps);
            $this->getPageView()->set('domains',$domains);
            $this->getPageView()->set('header',$header);
            $this->getPageView()->set('headers',$headers);
            $this->getPageView()->set('help',str_replace(array("\r","\n"),"",$help));
            $this->getPageView()->set('dataTypes',$dataTypes);  
            $this->getPageView()->set('sponsors',$sponsors); 
            $this->getPageView()->set('verticals',$verticals); 
            $this->getPageView()->set('pmtaPort',$pmtaPort);
        }
        
        /**
         * @name send
         * @description create a new mail instance to mail it with PowerMTA
         * @before init
         * @after closeConnection
         */
        public function send() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # get the connected user
                $user = Session::get('mailtng_connected_user');

                # retrieve all form data
                $data = Request::getAllDataFromPOST();

                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                # insert the user id 
                $data['user-id'] = Arrays::getElement($user,'id',0);
                    
                $isDrop = array_key_exists('drop',$data) ? true : false;
                $serversIds = Arrays::getElement($data,"servers",[]);
                $ips = Arrays::getElement($data,"selected-ips",[]);
                $splitType = Arrays::getElement($data,"emails-split-type",'ips');
                $lists = Arrays::getElement($data,"lists",null);
                $offerId = intval(Arrays::getElement($data,"offer",0));
                $emailsPerSeed = intval(Arrays::getElement($data,"emails-per-seed",1));
                $offset = intval(Arrays::getElement($data,"data-start",0));
                $limit = intval(Arrays::getElement($data,"data-count",0));
                $recipientsEmails = array_key_exists('recipients-emails',$data) ? explode(";",Arrays::getElement($data,'recipients-emails')) : null;
                
                $receipientsCount = 0;
                $queries = [];
                $serverCount = [];
                $ipCount = [];
                $serverIps = [];

                if(count($serversIds) == 0)
                {
                    die(json_encode(array("type" => "error" , "message" => "No Server Selected !")));
                }

                if(!in_array($user['application_role_id'],[1]))
                {
                    $servers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id FROM admin.servers WHERE server_type_id > 1 AND id IN (" . implode(',',$serversIds) . ") AND authorized_users LIKE '%,{$user['id']},%'",true);
                }
                else
                {
                    $servers = Server::all(true,array("id IN (" . implode(',',$serversIds) . ")",""),array('id'));
                }

                if(count($servers) != count($serversIds))
                {
                    die(json_encode(array("type" => "error" , "message" => "Please refresh your page , it looks like there are some unauthorized servers selected !")));
                }

                # ips validation
                if(count($ips) == 0)
                {
                    die(json_encode(array("type" => "error" , "message" => "Please check your IPs , it looks like there is no IPs selected !")));
                }

                # limit validation
                if($isDrop == true && $limit == 0)
                {
                    die(json_encode(array("type" => "error" , "message" => "Please Enter Data Limit !")));
                }
                
                # recipients validation
                if(count($recipientsEmails))
                {
                    $invalidEmails = false;

                    foreach ($recipientsEmails as $email) 
                    {
                        $email = preg_replace( "/\r|\n/","", trim($email));

                        if(!empty($email) && !filter_var($email,FILTER_VALIDATE_EMAIL))
                        {
                            $invalidEmails = true;
                        }

                        if(filter_var($email,FILTER_VALIDATE_EMAIL))
                        {
                            $receipientsCount++;
                        }
                    }

                    if($invalidEmails == true)
                    {
                        die(json_encode(array("type" => "error" , "message" => "Please check your recipients , it looks like there is some invalid emails !")));
                    }
                }

                # recipients count validation
                if ($receipientsCount == 0)
                {
                    die(json_encode(array("type" => "error" , "message" => "Please insert at least one recipient!")));
                }

                # lists validation
                if($isDrop == true)
                {
                    if(count($lists))
                    {
                        $query = "";

                        foreach ($lists as &$list) 
                        {
                            $list = explode("|",Crypto::AESDecrypt($list));

                            if(count($list) == 3)
                            {
                                $tableName = trim($list[2]);

                                $multi = (Strings::indexOf($tableName,'seeds') > -1) ? ",generate_series(1,$emailsPerSeed)" : '';

                                # switch the default database 
                                Database::switchToDefaultDatabase();

                                $authorised = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.data_lists WHERE name = '$tableName' AND authorized_users LIKE '%,{$user['id']},%'",true);

                                # switch the lists database 
                                Database::switchToDatabase('lists');

                                if(!in_array($user['application_role_id'],[1]) && count($authorised) == 0)
                                {
                                    die(json_encode(array("type" => "error" , "message" => "Please refresh the data lists section , There are some Unauthorized lists there !")));
                                }

                                $query .= "SELECT id,'{$tableName}' AS table,email$multi FROM $tableName UNION ALL ";

                            }
                        }

                        $query = rtrim($query,"UNION ALL ") . " WHERE COALESCE(offers_excluded,'') NOT SIMILAR TO '%(,$offerId,|,$offerId)%' OFFSET [offset] LIMIT [limit]";
                    }
                    else
                    {
                        die(json_encode(array("type" => "error" , "message" => "Please select the data lists you want to send to!")));
                    }

                    $srvs = [];

                    if($splitType == 'servers')
                    {   
                        foreach ($serversIds as $serversId)
                        {
                            $srv = [];

                            foreach ($ips as $ip)
                            {
                                $parts = explode('|', $ip);
                                $serverId = intval(Arrays::getElement($parts, 0));
                                $ipId = intval(Arrays::getElement($parts, 1));

                                if($serverId == $serversId)
                                {
                                    $srv[] = $ip;
                                }
                            }

                            $srvs[] = $srv;
                        }
                    }
                    else
                    {
                        $srv = [];

                        foreach ($ips as $ip)
                        {
                            $parts = explode('|', $ip);
                            $serverId = intval(Arrays::getElement($parts, 0));
                            $ipId = intval(Arrays::getElement($parts, 1));
                            $srv[] = $ip;
                        }

                        $srvs[] = $srv;
                    }

                    $ipOffset = $offset;
                    $srvLimit = ceil($limit / count($srvs));

                    foreach ($srvs as $srv)
                    {
                        $ipLimit = ceil($srvLimit / count($srv));

                        foreach ($srv as $ip)
                        {
                            $parts = explode('|',$ip);

                            if(count($parts) == 2)
                            {
                                $serverId = intval(Arrays::getElement($parts,0));
                                $ipId = intval(Arrays::getElement($parts,1));

                                if($serverId > 0 && $ipId > 0)
                                {
                                    $queries[$serverId][$ipId] = str_replace(['[offset]','[limit]'],[$ipOffset,$ipLimit],$query);
                                    $serverIps[$serverId][] = $ipId;
                                    $ipCount[$serverId][$ipId] += intval($ipLimit);
                                    $serverCount[$serverId] += intval($ipLimit);
                                    $ipOffset += $ipLimit;
                                }
                            } 
                        }
                    }
                }
                else
                {
                    foreach ($ips as $ip)
                    {
                        $parts = explode('|',$ip);

                        if(count($parts) == 2)
                        {
                            $serverId = intval(Arrays::getElement($parts,0));
                            $ipId = intval(Arrays::getElement($parts,1));

                            if($serverId > 0 && $ipId > 0)
                            {
                                $serverIps[$serverId][] = $ipId;
                            }
                        } 
                    }
                }

                # get negative file ( if any ) 
                $negativeFile = Request::getParameterFromFILES('negative-file');

                if($negativeFile != null && count($negativeFile) && strlen($negativeFile['tmp_name']) > 0)
                {
                    $negativeContent = file_get_contents(Arrays::getElement($negativeFile,'tmp_name'));

                    if(!empty($negativeContent))
                    {
                        $data['negative'] = $negativeContent;
                    }
                }
                
                # loop through all the selected servers to send the drop info  
                foreach ($serversIds as $serverId)
                {
                    $data['server-id'] = intval($serverId);
                    $data['queries'] = $queries[$data['server-id']];
                    $data['server-count'] = $serverCount[$data['server-id']];
                    $data['ip-count'] = $ipCount[$data['server-id']];
                    $data['ips'] = array_unique($serverIps[$data['server-id']]);
                    
                    # write the form into a file
                    $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'drops';
                    $fileName = 'drop_' . Strings::generateRandomText(5,true,true,true,false);

                    # convert the form data into json to store it into a file so that the mailing script will read it 
                    $jsonVersionOfDrop = json_encode($data);

                    if($jsonVersionOfDrop != '' && file_put_contents($fileDirectory . DS . $fileName, $jsonVersionOfDrop)) 
                    {  
                        # executing the script that handles mailing emails
                        $scriptPath = Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'drops' . DS . 'send.php'; 
                        System::executeCommand('nohup php ' . $scriptPath . ' ' . $fileName . '> /dev/null 2>&1 &');
                        //\ma\mailtng\output\PrintWriter::printValue(System::executeCommand('php ' . $scriptPath . ' ' . $fileName,true));
                    }
                    else
                    {
                        die(json_encode(array("type" => "error" , "message" => "Error occured while trying to create the drop file !")));
                    }
                }

                # show output message
                if(array_key_exists('drop',$data))
                {
                    die(json_encode(array("type" => "success" , "message" => "Drop has been procceed Successfully !")));
                }
                else
                {
                    die(json_encode(array("type" => "success" , "message" => "Test has been procceed Successfully !")));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name headers
         * @description the headers action
         * @before init
         * @after setMenu,closeConnection
         */
        public function headers() 
        { 
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';
            
            # get the connected user
            $user = Session::get('mailtng_connected_user'); 
                
            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_production_mail_headers',true);
                
                switch ($page) 
                {
                    case 'lists' :
                    {
                        # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'mail' . DS . 'headers' . DS . 'lists' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_production_mail_headers_list',true);
                        
                        # get the data from the database
                        $list = Database::getCurrentDatabaseConnector()->query()->from('admin.headers',array('id','name','value'))->where('user_id = ?',Arrays::getElement($user,'id'))->all();
                                    
                        # get all the columns names 
                        $columns = array('id','name','value');

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
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'mail' . DS . 'headers' . DS . 'add' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_production_mail_headers_add',true); 

                        break;
                    }
                    case 'edit' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;
                        
                        if(isset($id) && is_numeric($id))
                        {
                            
                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'mail' . DS . 'headers' . DS . 'edit' . '.' . $this->getDefaultExtension());
                            
                            # set the menu item to active 
                            $this->getMasterView()->set('menu_production_mail_headers_add',true);
                        
                            # retrieve the server by id
                            $header = Header::first(true,array("id" => $id));
                            
                            # set the data to the template
                            $this->getPageView()->set('header',$header);
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
                            $header = new Header(array("id" => $id));
                            $header->delete();
                            $message = "Record deleted successfully !";
                            $messageFlag = 'success';
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);

                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'types' . RDS . 'lists.html');
                        
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('mailtng_connected_user'); 

                        # retrieves the data from post
                        $id = Request::getParameterFromPOST('header-id');
                        $value = Request::getParameterFromPOST('header');
                        $name = Request::getParameterFromPOST('name');
                        
                        if(isset($name) && isset($value))
                        {
                            $message = "Something went wrong !";
                            $messageFlag = 'error';

                            if($id != NULL && is_numeric($id))
                            {
                                # update case
                                $header = new Header(array("id" => $id));
                                $header->setUser_id(intval(Arrays::getElement($user,'id',1)));
                                $header->setName($name);
                                $header->setValue(base64_encode($value));
                                $header->setType('');

                                $result = $header->save(); 

                                if($result > -1)
                                {
                                    $message = "Record updated succesfully !";
                                    $messageFlag = 'success';
                                }
                            }
                            else
                            {
                                # insert case
                                $header = new Header(array("id" => $id));
                                $header->setUser_id(intval(Arrays::getElement($user,'id',1)));
                                $header->setName($name);
                                $header->setValue(base64_encode($value));
                                $header->setType('');

                                $result = $header->save(); 

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
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail' . RDS . 'headers' . RDS . 'lists.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail' . RDS . 'headers' . RDS . 'lists.html');
                    }    
                }
            }
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

                if($this->getDefaultContentType() == 'text/json')
                {
                    # get the connected user
                    $user = Session::get('mailtng_connected_user');
            
                    # get all servers from the database
                    $serverProviders = ServerProvider::all(true,array('status_id = ? ',1),array('id','name'));
                    
                    if(!in_array($user['application_role_id'],[1]))
                    {
                        $servers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,provider_id FROM admin.servers WHERE server_type_id > 1 AND authorized_users LIKE '%,{$user['id']},%'",true);
                    }
                    else
                    {
                        $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','provider_id'));
                    }

                    # check for providers that has no servers in production 
                    foreach ($serverProviders as $index => $provider) 
                    {
                        $count = 0;

                        foreach ($servers as $server) 
                        {
                            if($server['provider_id'] == $provider['id'])
                            {
                                $count++;
                            }
                        }

                        if($count == 0)
                        {
                            unset($serverProviders[$index]);
                        }
                    }
            
                    die(json_encode(array("providers" => $serverProviders , "servers" => $servers)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getServers
         * @description gets the servers by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getIpsText() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $ips = Request::getParameterFromPOST("ips");
                
                if(isset($ips) && !empty($ips) && $this->getDefaultContentType() == 'text/json')
                {
                    $ips = base64_decode($ips);
                    $ips = explode(",",$ips);
                    $values = array();
                    
                    if(is_array($ips) && count($ips))
                    {
                        foreach ($ips as $ip) 
                        {
                            if(filter_var($ip,FILTER_VALIDATE_IP))
                            {
                                $values[] = "'" . $ip . "'";
                            }
                        }
                    }
                    
                    $results = Ip::all(true,array("value IN (?)","no_quote :" . implode(",",$values)));
                    
                    if(!empty($results))
                    {
                        $serverIds = array();
                        
                        foreach ($results as $ip) 
                        {
                            if(!in_array($ip['server_id'],$serverIds))
                            {
                                $serverIds[] = intval($ip['server_id']);
                            }
                        }
                    }

                    die(json_encode(array("ips" => $ips , "servers" => $serverIds)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getIps
         * @description gets the ips by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getIps() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $serverIds = isset($arguments) && count($arguments) ? implode(",",$arguments) : null;

                if(isset($serverIds) && !empty($serverIds) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the connected user
                    $user = Session::get('mailtng_connected_user');
            
                    # get the server from the database
                    if(!in_array($user['application_role_id'],[1]))
                    {
                        $servers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,main_ip FROM admin.servers WHERE server_type_id > 1 AND id IN ($serverIds) AND authorized_users LIKE '%,{$user['id']},%'",true);
                    }
                    else
                    {
                        $servers = Server::all(true,array("id IN ($serverIds)",""),array('id','name','main_ip'));
                    }
                    
                    if(count($servers) == count(explode(',',$serverIds)))
                    {
                        # get the ips from the database
                        $ips = Database::getCurrentDatabaseConnector()->executeQuery("SELECT i.id AS id,"
                                            . " i.value AS value,"
                                            . " i.rdns AS rdns,"
                                            . " s.name AS server,"
                                            . " s.id AS serverid"
                                            . " FROM admin.ips i"
                                            . " LEFT JOIN admin.servers s ON s.id = i.server_id"
                                            . " WHERE i.status_id = 1 AND i.server_id IN ($serverIds)"
                                            . " ORDER BY s.id ASC", true);
                    }

                    die(json_encode(array( "ips" => $ips , "servers" => $servers)));
                }
            } 
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
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
                $sponsorId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                
                if(isset($sponsorId) && is_numeric($sponsorId) && $this->getDefaultContentType() == 'text/json')
                {
                    $user = Session::get('mailtng_connected_user');
                    
                    # get the offers
                    if(!in_array($user['application_role_id'],[1]))
                    {
                        $offers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,production_id,flag FROM admin.offers WHERE sponsor_id = $sponsorId AND status_id = 1 AND authorized_users LIKE '%,{$user['id']},%'",true);
                    }
                    else
                    {
                        $offers = Offer::all(true,array('sponsor_id = ? AND status_id = ?',array($sponsorId,1)),array('id','name','production_id','flag'));
                    }

                    # print the result 
                    die(json_encode(array( "offers" => $offers)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getOfferCreatives
         * @description gets the offer creatives by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getOfferCreatives() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $offerId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                if(isset($offerId) && is_numeric($offerId) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the count of data by list
                    $creatives = OfferCreative::all(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('*'),'id','ASC');
                    
                    # print the result 
                    die(json_encode(array("creatives" => $creatives)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getOfferAssets
         * @description gets the offer assets by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getOfferAssets() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $offerId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                if(isset($offerId) && is_numeric($offerId) && $this->getDefaultContentType() == 'text/json')
                {
                    $offer = Offer::first(true,array('id=?',$offerId));
                    
                    # get the count of data by list
                    $creatives = OfferCreative::all(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('*'),'id','ASC');
                    $offerNames = OfferName::all(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('*'),'id','ASC');
                    $offerSubjects = OfferSubject::all(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('*'),'id','ASC');
                    
                    # print the result 
                    die(json_encode(array("vertical-id" => $offer['vertical_id'], "from-names" => $offerNames , "subjects" => $offerSubjects , "creatives" => $creatives)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getCreative
         * @description gets the creative by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getCreative() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $creativeId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                if(isset($creativeId) && is_numeric($creativeId) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the count of data by list
                    $creative = OfferCreative::first(true,array('id = ? AND status_id = ?',array($creativeId,1)),array('value'));
                    
                    $links = OfferLink::all(true,array('creative_id = ?',$creativeId));
                    $creativeHTML = $creative['value'];
                    
                    foreach ($links as $link) 
                    {
                        $tag = strtolower($link['type']) == 'preview' ? '[url]' : '[unsub]';      
                        $creativeHTML = str_replace($link['value'],'http://[domain]/' . $tag,$creativeHTML);
                    }
                    
                    $creativeHTML .= PHP_EOL . '<span style="color:#888;font-size:11px;font-family:verdana;display:block;text-align:center;margin-top:10px">click <a href="http://[domain]/[optout]">here</a> to remove yourself from our emails list</span>'; 

                    # print the result 
                    die(json_encode(array( "creative" => $creativeHTML)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name generateLinks
         * @description generate links for auto reply into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function generateLinks() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $offerId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                if(isset($offerId) && is_numeric($offerId) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the count of data by list
                    $creative = OfferCreative::first(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('id'));
                    $links = OfferLink::all(true,array('creative_id = ?',$creative['id']));
                    
                    $table = "<table class='table table-bordered table-striped table-condensed'>";
                    $table .= "<thead><tr>";
                    $table .= "<td>Type</td><td>Link</td>";
                    $table .= "</tr></thead>";
                    $table .= "<tbody>";

                    foreach ($links as $link) 
                    {
                        $table .= "<tr>";
                        $table .= "<td>" . strtoupper($link['type']) . "</td>";
                        $table .= "<td>" . 'http://[DOMAIN]' . RDS . 't?v=' . urlencode(Crypto::AESEncrypt(strtolower($link['type']) . '|0|no-table|0|no-email|' . $link['value'])) . "</td>";
                        $table .= "</tr>";
                    }

                    $table .= "<tr>";
                    $table .= "<td>SERVER UNSUB</td>";
                    $table .= "<td>" . 'http://[DOMAIN]' . RDS . 'unsub' . RDS . '?m=' . urlencode(Crypto::AESEncrypt('0|no-table|0|no-email')) . "</td>";
                    $table .= "</tr>";

                    $table .= "</tbody></table>";
                    
                    # print the result 
                    die(json_encode(array("links" => $table)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getDataLists
         * @description gets the data lists by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataLists() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $dataTypeId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                $ispId = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;
                $flag = isset($arguments) && count($arguments) > 2 ? $arguments[2] : null;
                $offerId = isset($arguments) && count($arguments) > 3 ? intval($arguments[3]) : 0;

                if(isset($dataTypeId) && is_numeric($dataTypeId) && $this->getDefaultContentType() == 'text/json')
                {
                    $lists = array();
                    
                    $ispResult = Isp::first(true,array('id = ?',$ispId),array('name'));
                    $typeResult = DataType::first(true,array('id = ?',$dataTypeId),array('name'));
                    
                    # switch th database 
                    Database::switchToDatabase('lists');

                    if(count($ispResult) && count($typeResult))
                    {
                        # get the connected user
                        $user = Session::get('mailtng_connected_user'); 
            
                        # specify the schema
                        $schema = strtolower(trim($ispResult['name']));
                        
                        # specify the type
                        $type = strtolower(trim($typeResult['name']));
                        
                        # get tables 
                        $tables = Database::getCurrentDatabaseConnector()->getAvailableTables($schema);

                        if(count($tables))
                        {
                            foreach ($tables as $table) 
                            {
                                if(!empty($table))
                                {                
                                    $tableCondition = $type . '_' . $flag;
                                    
                                    if(strpos($table,$tableCondition) > -1)
                                    {
                                        # switch the default database 
                                        Database::switchToDefaultDatabase();
                    
                                        $authorised = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.data_lists WHERE name = '{$schema}.{$table}' AND authorized_users LIKE '%,{$user['id']},%'",true);
                                        
                                        # switch the lists database 
                                        Database::switchToDatabase('lists');
                    
                                        if(in_array(Arrays::getElement($user,'application_role_id'),array(1)) || count($authorised))
                                        {
                                            $count = 0;
                                            $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) AS count FROM {$schema}.{$table}",true);

                                            if(count($result) && key_exists('count',$result[0]))
                                            {
                                                $count = intval($result[0]['count']);
                                                $remain = intval($result[0]['count']);
                                            }

                                            if($offerId > 0)
                                            {
                                                $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) AS count FROM {$schema}.{$table} WHERE COALESCE(offers_excluded,'') SIMILAR TO '%(,$offerId,|,$offerId)%'",true);

                                                if(count($result) && key_exists('count',$result[0]))
                                                {
                                                    $remain = $count - intval($result[0]['count']);
                                                }
                                            }

                                            $lists[] = array('id' => $schema . '.' . $table , 'name' => $table , 'count' => $count,'remain' => $remain);
                                        }
                                    }
                                }
                            }
                        }
                    }
                 
                    # print the result 
                    die(json_encode(array( "lists" => $lists)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getDataListChunks
         * @description gets the Data List chunks into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataListChunks() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $data = Request::getAllDataFromPOST();
                
                if(count($data) && key_exists('list',$data) && $data['list'] != '' && $this->getDefaultContentType() == 'text/json')
                {
                    $subLists = array(); 
  
                    Database::switchToDatabase('lists');
                    
                    $maxId = intval(Arrays::getElement(Arrays::getElement(Database::getCurrentDatabaseConnector()->executeQuery('SELECT id as max FROM ' . $data['list'] . ' ORDER BY id DESC LIMIT 1',true),0),'max'));
                    $maxPerList = 100000000000;
                    $loops = intval(ceil($maxId / $maxPerList));
                    $min = 1;
                    $max = $maxPerList;
                    $name = Arrays::getElement(explode(".",$data['list']),1);
                    $offerId = Arrays::getElement(explode(".",$data['offer-id']),0);
                    
                    $condition = $offerId > 0 ? "AND COALESCE(offers_excluded,'') NOT SIMILAR TO '%(,$offerId,|,$offerId)%'" : '';
                    
                    for ($index = 1; $index <= $loops; $index++) 
                    {                       
                        $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) as count FROM {$data['list']} WHERE id BETWEEN $min AND $max $condition", true);
                        $value = Crypto::AESEncrypt("$min|$max|{$data['list']}");
                        $subLists[] = array('value' => $value , 'name' => $name , 'count' => intval($result[0]['count']) , 'index' => $index);
                        $min = $max +1;
                        $max = $max + $maxPerList;
                    }

                    # print the result 
                    die(json_encode(array( "sub-lists" => $subLists)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
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
            $this->getMasterView()->set('menu_production_mail',true);
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

