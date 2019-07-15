<?php namespace ma\applications\mailtng\controllers {
    if (!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework MailTng Framework
     * @version 1.1
     * @author MailTng Team
     * @copyright Copyright (c) 2015 - 2016.
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
    use ma\mailtng\types\Strings as Strings;
    use ma\applications\mailtng\models\admin\Status as Status;
    use ma\applications\mailtng\models\data\DataType as DataType;
    use ma\applications\mailtng\models\data\DataList as DataList;
    use ma\applications\mailtng\models\data\Fresh as Fresh;
    use ma\applications\mailtng\models\data\Opener as Opener;
    use ma\applications\mailtng\models\data\Clicker as Clicker;
    use ma\applications\mailtng\models\data\Lead as Lead;
    use ma\applications\mailtng\models\admin\Isp as Isp;
    use ma\applications\mailtng\models\admin\Server as Server;
    use ma\applications\mailtng\models\admin\Sponsor as Sponsor;
    use ma\applications\mailtng\models\admin\Offer as Offer;
    use ma\applications\mailtng\models\admin\Proccess as Proccess;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\os\System as System;
    use ma\mailtng\files\Paths as Paths;
    use ma\mailtng\globals\Server as GloblServers;
    use ma\mailtng\exceptions\types\SQLException as SQLException;
    use ma\mailtng\exceptions\types\PageException as PageException;
    /**
     * @name Data.controller
     * @description The Data controller
     * @package ma\applications\mailtng\controllers
     * @category Controller
     * @author MailTng Team
     */
    class Data extends Controller
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'show.html');
        }
               
        /**
         * @name types
         * @description the data types action
         * @before init
         * @after setMenu,closeConnection
         */
        public function types()
        {
            $arguments = func_get_args();
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';
            if(isset($page))
            {
                # set the menu item to active
                $this->getMasterView()->set('menu_admin_data_types',true);
                
                switch ($page)
                {
                    case 'lists' :
                    {
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'types' . DS . 'lists' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_types_list',true);
                        
                        # get the data from the database
                        $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT dt.id AS id,"
                                                        . " dt.name AS type,"
                                                        . " s.name AS status,"
                                                        . " uc.username AS created_by,"
                                                        . " dt.created_at AS created_date,"
                                                        . " up.username AS last_updated_by,"
                                                        . " dt.last_updated_at AS last_updated_at"
                                                        . " FROM admin.data_types dt"
                                                        . " LEFT JOIN admin.users uc ON uc.id = dt.created_by"
                                                        . " LEFT JOIN admin.status s ON s.id = dt.status_id"
                                                        . " LEFT JOIN admin.users up ON up.id = dt.last_updated_by"
                                                        . " ORDER BY dt.id", true);
                                    
                        # get all the columns names
                        $columns = array('id','type','status','created_by','created_date','last_updated_by','last_updated_at');
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
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'types' . DS . 'add' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_providers_add',true);
                        
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
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'types' . DS . 'edit' . '.' . $this->getDefaultExtension());
                            
                            # set the menu item to active
                            $this->getMasterView()->set('menu_admin_data_providers_add',true);
                        
                            # retrieve the server by id
                            $dataType = DataType::first(true,array('id = ?',$id));
                            # get status list
                            $status = Status::all(true,array(),array('id','name'),'id','ASC');
                            # set the data to the template
                            $this->getPageView()->set('type',$dataType);
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
                            $type = new DataType(array("id" => $id));
                            $type->delete();
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
                        $id = Request::getParameterFromPOST('type-id');
                        $status = Request::getParameterFromPOST('status-id');
                        $type = Request::getParameterFromPOST('type');
                        
                        if(isset($type))
                        {
                            $message = "Something went wrong !";
                            $messageFlag = 'error';
                            if($id != NULL && is_numeric($id))
                            {
                                # update case
                                $dataType = new DataType(array("id" => $id));
                                $dataType->setStatus_id(intval($status));
                                $dataType->setName($type);
                                $dataType->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $dataType->setLast_updated_at(date("Y-m-d"));
                                $result = $dataType->save();
                                if($result > -1)
                                {
                                    $message = "Record updated succesfully !";
                                    $messageFlag = 'success';
                                }
                            }
                            else
                            {
                                # insert case
                                $dataType = new DataType(array("id" => $id));
                                $dataType->setStatus_id(intval($status));
                                $dataType->setName($type);
                                $dataType->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                $dataType->setCreated_at(date("Y-m-d"));
                                $dataType->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $dataType->setLast_updated_at(date("Y-m-d"));
                                $result = $dataType->save();
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
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'types' . RDS . 'lists.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'types' . RDS . 'lists.html');
                    }    
                }
            }
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
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';
            if(isset($page))
            {
                # set the menu item to active
                $this->getMasterView()->set('menu_admin_data_lists',true);
                
                switch ($page)
                {
                    case 'all' :
                    {
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'lists' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_all',true);
                        # get tables
                        $lists = DataList::all(true);
                        
                        # switch th database
                        Database::switchToDatabase('lists');
                        
                        $res = array();
                                
                        if(count($lists))
                        {
                            foreach ($lists as $list)
                            {
                                if(!empty($list))
                                {
                                    $isp = Arrays::getElement(explode(".",$list['name']),0);
                                    $tablePrefix = Arrays::getElement(explode(".",$list['name']),1);
                                    $type = Arrays::getElement(explode('_', $tablePrefix),0);
                                    $flag = Arrays::getElement(explode('_', $tablePrefix),1);
                                    $flag = is_numeric($flag) ? '-' : $flag;
                                    
                                    # get the count
                                    $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) AS count FROM {$list['name']}",true);
                                    $count = 0;
                                    
                                    if(count($result))
                                    {
                                        $count = intval($result[0]['count']);
                                    }
                                    
                                    $res[] = array(
                                        'list_name' => $tablePrefix,
                                        'type' => ucfirst($type),
                                        'flag' => strtoupper($flag),
                                        'isp' => ucfirst($isp),
                                        'list_count' => $count
                                    );
                                }
                            }
                        }
                        # get all the columns names
                        $columns = array('list_name','type','flag','ISP','list_count');
                       
                        # set the list into the template data system
                        $this->getPageView()->set('list',$res);
                        # set the columns list into the template data system
                        $this->getPageView()->set('columns',$columns);
                        
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'add' :
                    {
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'add' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_add',true);
                        
                        # get status list
                        $status = Status::all(true,array(),array('id','name'),'id','ASC');
                        
                        # get isps list
                        $isps = Isp::all(true,array(),array('id','name'),'id','ASC');
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        
                        # set the list into the template data system
                        $this->getPageView()->set('isps',$isps);
                        $this->getPageView()->set('status',$status);
                        break;
                    }
                    case 'seeds' :
                    {
                        # retrieve all form data
                        $data = Request::getAllDataFromPOST();
                        
                        if(isset($data) && count($data))
                        {
                            # prevent layout to be displayed
                            $this->setShowMasterView(false);
                            $this->setShowPageView(false);
                            
                            $listName = Request::getParameterFromPOST("data-list");
                            $emails = Request::getParameterFromPOST("emails");
                            
                            $messageFlag = 'error';
                            $message = "Please check be sure that you have selected everything!";
                                
                            if(!empty($emails) && !empty($listName))
                            {
                                $emails = array_values(array_filter(explode(PHP_EOL,$emails), "trim"));
                                
                                if(count($emails))
                                {
                                    # switch th database
                                    Database::switchToDatabase('lists');
                                    $schema = Arrays::getElement(explode('.',$listName),0);
                                    $list = Arrays::getElement(explode('.',$listName),1);
                                    
                                    # delete the old ones
                                    Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM $listName");
                                    Database::getCurrentDatabaseConnector()->executeQuery("ALTER SEQUENCE {$schema}.seq_id_{$list} RESTART WITH 1;");
                                    
                                    foreach ($emails as $email)
                                    {
                                        $list = new Fresh();
                                        $list->setSchema($schema);
                                        $list->setTable($list);
                                        $list->setEmail(trim($email));
                                        $list->save();
                                    }
                                    $messageFlag = "success";
                                    $message = "List Updated Successfully !";
                                }  
                            }
                            
                            # stores the message in the session
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'seeds.html');
                        }
                        
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'seeds' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_seeds',true);
                        # get all isps from the database
                        $isps = Isp::all(true,array('status_id = ? ',1));
                        
                        # set the list into the template data system
                        $this->getPageView()->set('isps',$isps);
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('mailtng_connected_user');
            
                        # retrieves the data from post
                        $name = Request::getParameterFromPOST('list-name','');
                        $type = Request::getParameterFromPOST('type');
                        $ispId = Request::getParameterFromPOST('isp-id');
                        $flag = Request::getParameterFromPOST('flag');
                        $emailsFile = Request::getParameterFromFILES('emails');
                        $maxPerList = intval(Request::getParameterFromPOST('devide-by')) > 5000 ? intval(Request::getParameterFromPOST('devide-by')) : 5000;
                        
                        if(isset($ispId))
                        {
                            $isp = Isp::first(true,array('id = ?',$ispId));
                            $schema = strtolower($isp['name']);
                            $message = "Something went wrong !";
                            $messageFlag = 'error';
                            
                            $emailsFromFile = array_unique(array_values(array_filter(file(Arrays::getElement($emailsFile,'tmp_name'),FILE_SKIP_EMPTY_LINES), "trim")));
                            $filesCount = intval(ceil(count($emailsFromFile) / $maxPerList));
                            
                            if(count($emailsFromFile) == 0)
                            {
                                $message = "List empty !";
                            }
                            else if(count($isp) == 0)
                            {
                                $message = "Please select an ISP !";
                            }
                            else
                            {
                                # switch to the apropriate database
                                Database::switchToDatabase('lists');
                                
                                # generate table name prefix;
                                $tablePrefix = strlen(trim($name)) > 0 ? strtolower($type . "_" . $flag . "_" . $name) : strtolower($type . "_" . $flag);
                                # get all the tables
                                $lists = Database::getCurrentDatabaseConnector()->getAvailableTables($schema);
                                
                                $tableIndex = 0;
                                
                                # get the last index
                                foreach ($lists as $list)
                                {
                                    if(strpos($list,$tablePrefix) > -1)
                                    {
                                        $tmp = explode('_',$list);
                                        $currentIndex = intval(Arrays::getElement($tmp,(count($tmp)-1)));
                                        
                                        if($tableIndex < $currentIndex)
                                        {
                                            $tableIndex = $currentIndex;
                                        }
                                    }
                                }
                                
                                $tableIndex++;
                                $maxIndex = 0;
                                
                                for($i = 0; $i < $filesCount; $i++)
                                {
                                    # switch to the apropriate database
                                    Database::switchToDatabase('lists'); 
                                
                                    $header = "id;email;offers_excluded";
                                    $more = ";";
                                    $tableName = $tablePrefix . '_' . intval($tableIndex);
                                    # create the table
                                    switch ($type)
                                    {
                                        case 'fresh':
                                        case 'seeds':
                                        {
                                            Fresh::synchronizeWithDatabase($tableName,$schema);
                                            break;
                                        }
                                        case 'openers':
                                        {
                                            Opener::synchronizeWithDatabase($tableName,$schema);
                                            $header = "id;email;action_date;offers_excluded;verticals;agent;ip;country;region;city;language;device_type;device_name;os;browser_name;browser_version";
                                            $more = ";;;;;;;;;;;;;;";
                                            break;
                                        }
                                        case 'clickers':
                                        {
                                            Clicker::synchronizeWithDatabase($tableName,$schema);
                                            $header = "id;email;action_date;offers_excluded;verticals;agent;ip;country;region;city;language;device_type;device_name;os;browser_name;browser_version";
                                            $more = ";;;;;;;;;;;;;;";
                                            break;
                                        }
                                        case 'leads':
                                        {
                                            Lead::synchronizeWithDatabase($tableName,$schema);
                                            $header = "id;email;action_date;offers_excluded;verticals;agent;ip;country;region;city;language;device_type;device_name;os;browser_name;browser_version";
                                            $more = ";;;;;;;;;;;;;;";
                                            break;
                                        }   
                                    }
                                    # creating csv file to store it into the database
                                    $csvData = $header . PHP_EOL;
                                    $id = 1;
                                    
                                    for ($j = $maxIndex; $j < ($maxIndex + $maxPerList); $j++)
                                    {
                                        $email = trim(preg_replace('/\s\s+/','',$emailsFromFile[$j]));
                                        
                                        if(filter_var($email,FILTER_VALIDATE_EMAIL))
                                        {
                                            $csvData .= "{$id};{$email}{$more}" . PHP_EOL;
                                            $id++;
                                        }
                                        
                                        if($j == (count($emailsFromFile) - 1))
                                        {
                                            break;
                                        }
                                    }
                                    
                                    # create the file name
                                    $randomNumber = rand(10,100);
                                    $csvFileName = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'csv' . DS . "data_file_{$tableName}_$randomNumber";
                                    # store the file into the server directory and store it into the database
                                    if(file_put_contents($csvFileName,trim($csvData,PHP_EOL)))
                                    {
                                        # get the data from the database
                                        Database::getCurrentDatabaseConnector()->executeQuery("COPY {$schema}.{$tableName} FROM '$csvFileName' WITH CSV HEADER DELIMITER AS ';' NULL AS '';");
                                        
                                        # delete the csv file
                                        unlink($csvFileName);
                                    }
                                    else
                                    {
                                        throw new SQLException("Could not store the csv file into the directory !");
                                    }
                                    
                                    # switch to default database
                                    Database::switchToDefaultDatabase();
                                    
                                    # create the list
                                    $list = new DataList();
                                    $list->setName("{$schema}.{$tableName}");
                                    $list->setIsp_id($ispId);
                                    $list->setFlag($flag);
                                    $list->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                    $list->setCreated_at(date("Y-m-d"));
                                    $list->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                    $list->setLast_updated_at(date("Y-m-d"));
                                    $list->save();
                                    
                                    $message = "List Created Successfully !";
                                    $messageFlag = 'success';
                                    $maxIndex = ($maxIndex + $maxPerList);
                                    $tableIndex++;
                                }
                            }
                            # stores the message in the session
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                        }
                        # redirect to show list
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
                    }
                    case 'delete' :
                    {
                        $list = base64_decode(str_replace('_','=',$arguments[1]));
                        
                        $message = "Something went wrong !";
                        $messageFlag = 'error';
                            
                        if(!empty($list) && (strpos($list,'fresh') > -1 || strpos($list,'seeds') > -1))
                        {
                            # switch to data database
                            Database::switchToDatabase('lists');
                            
                            # drop the list
                            Database::getCurrentDatabaseConnector()->executeQuery("DROP TABLE $list",true);
                            
                            # switch to default database
                            Database::switchToDefaultDatabase();
                                  
                            # drop the list
                            Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.data_lists WHERE name='$list'",true);
                            
                            $message = "Record deleted successfully !";
                            $messageFlag = 'success';
                        }
                        
                        # stores the message in the session
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);
                 
                        # redirect to show list
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
                    }
                    case 'clean' :
                    {
                        # retrieve all form data
                        $data = Request::getAllDataFromPOST();
                        
                        if(isset($data) && count($data))
                        {
                            # get the connected user
                            $user = Session::get('mailtng_connected_user');
                            
                            # prevent layout to be displayed
                            $this->setShowMasterView(false);
                            $this->setShowPageView(false);
                            
                            $serverId = intval(Request::getParameterFromPOST("server"));
                            $listName = Request::getParameterFromPOST("data-list");
                            
                            $messageFlag = 'error';
                            $message = "Please check be sure that you have selected everything!";
                                
                            if($serverId > 0 && !empty($listName))
                            {
                                # insert new proccess into the database
                                $proccess = new Proccess();
                                $proccess->setUser_id(intval(Arrays::getElement($user,'id',1)));
                                $proccess->setName('bounce_clean_' . Strings::generateRandomText(10,true,false,true,false));
                                $proccess->setType('clean_bounce');
                                $proccess->setStatus('stand by');
                                $proccess->setProgress('0%');
                                $proccess->setStart_time(date('Y-m-d H:i:s'));
                                $proccessId = $proccess->save(true);
                                # execute the script
                                System::executeCommand('nohup php ' . Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'cleaning' . DS . 'bounce.php ' . $proccessId . ' ' . $serverId . ' ' . base64_encode($listName) . '> /dev/null 2>&1 &');
                                # stores the message in the session
                                $messageFlag = 'success';
                                $message = 'Bounce Clean proccess has been started !';
                            }
                            
                            # stores the message in the session
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'clean.html');
                        }
                        
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'clean' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_clean',true);
                        # get all servers from the database
                        $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name'));
                        # get all isps from the database
                        $isps = Isp::all(true,array('status_id = ? ',1));
                        
                        # get the data from the database
                        $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT pr.id AS id,"
                                                        . " pr.name AS proccess_name,"
                                                        . " pr.type AS type,"
                                                        . " uc.username AS mailer,"
                                                        . " pr.status AS status,"
                                                        . " pr.start_time AS start_time,"
                                                        . " pr.finish_time AS finish_time,"
                                                        . " pr.progress AS progress,"
                                                        . " pr.data AS data"
                                                        . " FROM admin.proccesses pr"
                                                        . " LEFT JOIN admin.users uc ON uc.id = pr.user_id"
                                                        . " WHERE pr.type = 'clean_bounce'"
                                                        . " ORDER BY pr.id", true);
                                    
                        # get all the columns names
                        $columns = array('id','proccess_name','type','mailer','status','start_time','finish_time','progress','data_list','hard_bounce_emails');
     
                        # parse data
                        if(count($list))
                        {
                            foreach ($list as &$row)
                            {
                                $row['data_list'] = '-';
                                $row['hard_bounce_emails'] = '0';
                                
                                if(!empty($row['data']) && !empty(json_decode($row['data'],true)))
                                {
                                    $data = json_decode($row['data'],true);
                                    
                                    $row['data_list'] = $data['data_list'];
                                    $row['hard_bounce_emails'] = $data['hard_bounce_emails'];
                                }
                                
                                unset($row['data']);
                            }
                        }
                        # set the list into the template data system
                        $this->getPageView()->set('isps',$isps);
                        $this->getPageView()->set('servers',$servers);
                        $this->getPageView()->set('list',$list);
                        $this->getPageView()->set('columns',$columns);
                        
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'download' :
                    {
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'download' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_download',true);
                        # get all isps from the database
                        $isps = Isp::all(true,array('status_id = ? ',1));
                        
                        # set the list into the template data system
                        $this->getPageView()->set('isps',$isps);
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'suppression' :
                    {
                        # retrieve all form data
                        $data = Request::getAllDataFromPOST();
                        
                        if(isset($data) && count($data))
                        {
                            $messageFlag = "danger";
                            $message = "Please check be sure that you have selected everything!";
                        
                            # get the connected user
                            $user = Session::get('mailtng_connected_user'); 
                            
                            # prevent layout to be displayed
                            $this->setShowMasterView(false);
                            $this->setShowPageView(false);
                            
                            $offerId = intval(Request::getParameterFromPOST("offer"));
                            $link = Arrays::getElement($data,'direct-link');
                            $ispId = intval(Arrays::getElement($data,'isp-id'));
                            $offer = Offer::first(true,['id = ?',$offerId]);
                            $isp = Isp::first(true,['id = ?',$ispId]);
                            
                            if(count($offer))
                            {
                                if(!filter_var($link,FILTER_VALIDATE_URL))
                                {
                                    # stores the message in the session 
                                    Session::set('proccess_message_flag','danger');
                                    Session::set('proccess_message','Please check the direct link that you have provided !');

                                    Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'suppression.html');
                                }
                            
                                # insert new proccess into the database
                                $proccess = new Proccess();
                                $proccess->setUser_id(intval(Arrays::getElement($user,'id',1)));
                                $proccess->setName('offer_suppression_' . Strings::generateRandomText(10,true,false,true,false));
                                $proccess->setType('offer_suppression');
                                $proccess->setStatus('downloading ..');
                                $proccess->setProgress('0%');
                                $proccess->setStart_time(date('Y-m-d H:i:s'));
                                $proccessId = $proccess->save(true);

                                # execute the script
                                shell_exec('nohup sh  ' . Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'cleaning' . DS . 'suppression.sh suppression ' . $proccessId . ' ' . $offerId . ' ' . strtolower($isp['name']) . ' ' . base64_encode($link) . ' > /tmp/suppression.log &');
                 
                                # stores the message in the session 
                                $messageFlag = 'success';
                                $message = 'Suppression proccess has been started !';

                            }
                            
                            # stores the message in the session 
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);

                            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'suppression.html');
                        }
                        
                        # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'suppression' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_admin_data_lists_suppression',true);
                        
                        # get all sponsors from the database
                        $sponsors = Sponsor::all(true,array('status_id = ? ',1));
                        
                        # get the data from the database
                        $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT pr.id AS id,"
                                                        . " pr.name AS proccess_name,"
                                                        . " pr.type AS type," 
                                                        . " uc.username AS mailer,"
                                                        . " pr.status AS status,"  
                                                        . " pr.start_time AS start_time," 
                                                        . " pr.finish_time AS finish_time," 
                                                        . " pr.progress AS progress,"
                                                        . " pr.data AS data" 
                                                        . " FROM admin.proccesses pr"
                                                        . " LEFT JOIN admin.users uc ON uc.id = pr.user_id"
                                                        . " WHERE pr.type = 'offer_suppression'"
                                                        . " ORDER BY pr.id", true);
                                    
                        # get all the columns names 
                        $columns = array('id','proccess_name','type','mailer','status','start_time','finish_time','progress','offer','suppression_emails');
     
                        # parse data 
                        if(count($list))
                        {
                            foreach ($list as &$row) 
                            {
                                $row['offer'] = '-';
                                $row['suppression_emails'] = '0';
                                
                                if(!empty($row['data']) && !empty(json_decode($row['data'],true)))
                                {
                                    $data = json_decode($row['data'],true);
                                    $row['offer'] = $data['offer'];
                                    $row['suppression_emails'] = $data['suppression_emails'];
                                }
                                
                                unset($row['data']);
                            }
                        }
                        
                        # get isps list 
                        $isps = Isp::all(true,array(),array('id','name'),'id','ASC');
                        
                        $this->getPageView()->set('sponsors',$sponsors); 
                        $this->getPageView()->set('isps',$isps);
                        $this->getPageView()->set('list',$list);
                        $this->getPageView()->set('columns',$columns);
                        
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
                    }    
                }
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
                $ispId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                $flag = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;
                
                if(isset($ispId) && is_numeric($ispId) && $this->getDefaultContentType() == 'text/json')
                {
                    $lists = array();
                    
                    $ispResult = Isp::first(true,array('id = ?',$ispId),array('name'));
                    
                    # switch th database
                    Database::switchToDatabase('lists');
                    if(count($ispResult))
                    {
                        # specify the schema
                        $schema = strtolower(trim($ispResult['name']));
                        
                        # specify the type
                        $type = 'fresh';
                        
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
                                        $lists[] = array('id' => $schema . '.' . $table , 'name' => $table);
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
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
            }
        }
        
        /**
         * @name getDataSeedsLists
         * @description gets the data lists by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataSeedsLists()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                $arguments = func_get_args();
                $ispId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                
                if(isset($ispId) && is_numeric($ispId) && $this->getDefaultContentType() == 'text/json')
                {
                    $lists = array();
                    
                    $ispResult = Isp::first(true,array('id = ?',$ispId),array('name'));
                    
                    # switch th database
                    Database::switchToDatabase('lists');
                    if(count($ispResult))
                    {
                        # specify the schema
                        $schema = strtolower(trim($ispResult['name']));
                        
                        # specify the type
                        $type = 'seeds';
                        
                        # get tables
                        $tables = Database::getCurrentDatabaseConnector()->getAvailableTables($schema);
                        
                        if(count($tables))
                        {
                            foreach ($tables as $table)
                            {
                                if(!empty($table))
                                {
                                    $tableCondition = $type . '_';
                                    
                                    if(strpos($table,$tableCondition) > -1)
                                    {
                                        $lists[] = array('id' => $schema . '.' . $table , 'name' => $table);
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
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
            }
        }
        /**
         * @name getDataListCount
         * @description gets the data count by list into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataListCount()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                $arguments = func_get_args();
                $listId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                
                if(isset($listId) && $this->getDefaultContentType() == 'text/json')
                {
                    $count = 0;
                    
                    $listName = base64_decode(str_replace('_','=',$listId));
                    
                    # switch th database
                    Database::switchToDatabase('lists');
                    
                    $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) AS count FROM {$listName}",true);
                    
                    if(count($result))
                    {
                        $count = intval($result[0]['count']);
                    }
                 
                    # print the result
                    die(json_encode(array( "count" => $count)));
                }
            }
            else
            {
                # redirect to show list
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
            }
        }
        
        /**
         * @name updateBounceProgress
         * @description update proccess progress
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function updateBounceProgress()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                if($this->getDefaultContentType() == 'text/json')
                {
                    $progress = '0%';
                    $emails = '0';
                    
                    $arguments = func_get_args();
                    $proccessId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                    if(isset($proccessId) && is_numeric($proccessId) && $this->getDefaultContentType() == 'text/json')
                    {
                        $proccess = Proccess::first(true,array('id = ?',$proccessId),array('id','status','progress','data'));
                        
                        if(isset($proccess) && $proccess['status'] == 'in progress')
                        {
                            $progress = $proccess['progress'];
                            
                            if(!empty($proccess['data']) && !empty(json_decode($proccess['data'],true)))
                            {
                                $data = json_decode($proccess['data'],true);
                                
                                if(key_exists('hard_bounce_emails', $data))
                                {
                                    $emails = $data['hard_bounce_emails'];
                                }
                            }
                        } 
                    } 
                    
                    die(json_encode(array("progress" => $progress , "emails" => $emails)));
                }
            }
        }
        
        /**
         * @name updateBounceProgress
         * @description update proccess progress
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function downloadData()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                $csv = '';
                
                if($this->getDefaultContentType() == 'text/json')
                {
                    # prevent layout to be displayed
                    $this->setShowMasterView(false);
                    $this->setShowPageView(false);
                    $listName = Request::getParameterFromPOST("data-list");
                    if(!empty($listName))
                    {
                        Database::switchToDatabase('lists');
                        $emails = Database::getCurrentDatabaseConnector()->query()->from($listName,array('email'))->all();
                        $name = Arrays::getElement(explode('.',$listName),1);
                        foreach ($emails as $row)
                        {
                            $email = preg_replace( "/\r|\n/","", trim($row['email']));
                            $csv .= $email . PHP_EOL;
                        }
                    }
                    
                    die(json_encode(array("name" => $name , "content" => $csv)));
                }
            }
        }
        
        /**
         * @name updateSuppressionProgress
         * @description update proccess progress
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function updateSuppressionProgress()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                if($this->getDefaultContentType() == 'text/json')
                {
                    $progress = '0%';
                    $emails = '0';
                    
                    $arguments = func_get_args();
                    $proccessId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                    if(isset($proccessId) && is_numeric($proccessId) && $this->getDefaultContentType() == 'text/json')
                    {
                        $proccess = Proccess::first(true,array('id = ?',$proccessId),array('id','status','progress','data'));
                        
                        if(isset($proccess) && $proccess['status'] == 'in progress')
                        {
                            $progress = $proccess['progress'];
                            
                            if(!empty($proccess['data']) && !empty(json_decode($proccess['data'],true)))
                            {
                                $data = json_decode($proccess['data'],true);
                                
                                if(key_exists('suppression_emails', $data))
                                {
                                    $emails = $data['suppression_emails'];
                                }
                            }
                        } 
                    } 
                    
                    die(json_encode(array("progress" => $progress , "emails" => $emails)));
                }
            }
        }
        
        /**
         * @name getDataListSeedsEmails
         * @description gets the data emails by list into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataListSeedsEmails()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                $arguments = func_get_args();
                $listId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                
                if(isset($listId) && $this->getDefaultContentType() == 'text/json')
                {
                    $emails = array();
                    
                    $listName = base64_decode(str_replace('_','=',$listId));
                    
                    if(strpos($listName,'seeds') > -1)
                    {
                        # switch th database
                        Database::switchToDatabase('lists');
                        $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT email FROM {$listName}",true);
                        if(count($result))
                        {
                            foreach ($result as $row)
                            {
                                $emails[] = trim($row['email']);
                            }
                        }
                    }
                    
                    # print the result
                    die(json_encode(array( "emails" => implode(PHP_EOL,$emails))));
                }
            }
            else
            {
                # redirect to show list
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
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
            $this->getMasterView()->set('menu_admin_data',true);
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
