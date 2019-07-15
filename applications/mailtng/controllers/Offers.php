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
    use ma\mailtng\application\Application as Application;
    use ma\mailtng\database\Database as Database;
    use ma\mailtng\http\Request as Request;
    use ma\mailtng\http\Response as Response;
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\www\URL as URL;  
    use ma\mailtng\globals\Server as GloblServers;
    use ma\mailtng\types\Arrays as Arrays;
    use ma\applications\mailtng\models\admin\Sponsor as Sponsor;
    use ma\applications\mailtng\models\admin\Status as Status;
    use ma\applications\mailtng\helpers\PagesHelper as PagesHelper;
    use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
    use ma\mailtng\ssh2\SSH as SSH;
    use ma\mailtng\api\Api as Api;
    use ma\applications\mailtng\models\admin\Offer as Offer;
    use ma\applications\mailtng\models\admin\OfferFromName as OfferName;
    use ma\applications\mailtng\models\admin\OfferSubject as OfferSubject;
    use ma\applications\mailtng\models\admin\OfferCreative as OfferCreative;
    use ma\applications\mailtng\models\admin\OfferLink as OfferLink;
    use ma\applications\mailtng\models\admin\Vertical as Vertical;
    use ma\applications\mailtng\models\admin\Server as Server;
    /**
     * @name            Offers.controller 
     * @description     The Offers controller
     * @package		ma\applications\mailtng\controllers
     * @category        Controller
     * @author		MailTng Team			
     */
    class Offers extends Controller 
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
        }
        
        /**
         * @name reset
         * @description the reset action
         * @before init
         * 
         * @after setMenu,closeConnection
         */
        public function reset() 
        { 
            # drop tables 
            Database::getCurrentDatabaseConnector()->dropTable("admin","offers");
            Database::getCurrentDatabaseConnector()->dropTable("admin","offer_names");
            Database::getCurrentDatabaseConnector()->dropTable("admin","offer_subjects");
            Database::getCurrentDatabaseConnector()->dropTable("admin","offer_creatives");
            Database::getCurrentDatabaseConnector()->dropTable("admin","offer_links");
            
            # create the tables again 
            Offer::synchronizeWithDatabase();
            OfferName::synchronizeWithDatabase();
            OfferSubject::synchronizeWithDatabase();
            OfferCreative::synchronizeWithDatabase();
            OfferLink::synchronizeWithDatabase();
            
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
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
            $this->getMasterView()->set('menu_admin_offers',true);
            $this->getMasterView()->set('menu_admin_offers_lists',true);

            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT o.id AS id,"
                                            . " o.name AS offer_name,"
                                            . " sp.name AS sponsor_name,"
                                            . " o.campaign_id AS campaign_id,"
                                            . " o.production_id AS production_id,"
                                            . " o.flag AS flag,"
                                            . " o.rate AS rate,"
                                            . " v.name AS vertical,"
                                            . " s.name AS status" 
                                            . " FROM admin.offers o"
                                            . " LEFT JOIN admin.sponsors sp ON sp.id = o.sponsor_id"
                                            . " LEFT JOIN admin.verticals v ON v.id = o.vertical_id"
                                            . " LEFT JOIN admin.users uc ON uc.id = o.created_by"
                                            . " LEFT JOIN admin.status s ON s.id = o.status_id"
                                            . " LEFT JOIN admin.users up ON up.id = o.last_updated_by"
                                            . " ORDER BY o.id", true);
                                    
            # get all the columns names 
            $columns = array('id','offer_name','sponsor_name','campaign_id','production_id','flag','rate','vertical','status');

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
            $this->getMasterView()->set('menu_admin_offers',true);
            $this->getMasterView()->set('menu_admin_offers_add',true);
            
            # get status list 
            $sponsors = Sponsor::all(true,array('status_id = ?',1),array('id','name'),'id','ASC');
            $status = Status::all(true,array(),array('id','name'),'id','ASC');

            # set the list into the template data system 
            $this->getPageView()->set('sponsors',$sponsors);
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
            $this->getMasterView()->set('menu_admin_offers',true);
            $this->getMasterView()->set('menu_admin_offers_add',true);
            
            $arguments = func_get_args(); 
            $id = isset($arguments) && count($arguments) ? $arguments[0] : null;

            if(isset($id) && is_numeric($id))
            {
                # retrieve the data
                $offer = Offer::first(true,array('id = ?',$id));
                $sponsors = Sponsor::all(true);
                $verticals = Vertical::all(true);
                $status = Status::all(true);

                # get creatives
                $creativesList = Database::getCurrentDatabaseConnector()->executeQuery("SELECT c.id AS id,"
                                                . " c.value AS creative_value,"
                                                . " st.name AS status,"
                                                . " uc.username AS created_by,"
                                                . " st.created_at AS created_date,"
                                                . " up.username AS last_updated_by,"
                                                . " st.last_updated_at AS last_updated_at"
                                                . " FROM admin.offer_creatives c"
                                                . " LEFT JOIN admin.users uc ON uc.id = c.created_by"
                                                . " LEFT JOIN admin.status st ON st.id = c.status_id"
                                                . " LEFT JOIN admin.users up ON up.id = c.last_updated_by"
                                                . " WHERE c.offer_id = $id"
                                                . " ORDER BY c.id", true);
                        
                # add index to the creatives and get links
                foreach ($creativesList as &$creative) 
                {
                    $creative['creative'] = "creative_" . $creative['id'];
                    $creative['creative_links'] = "";
                    
                    $links = OfferLink::all(true,array('creative_id = ?',$creative['id']));
                    
                    if(count($links))
                    {
                        foreach ($links as $link) 
                        {
                            if(count($link))
                            {
                               $creative['creative_links'] .= "<tr><td>{$link['type']}</td><td>{$link['value']}</td></tr>";
                            }
                        }
                    }
                    
                    $creative['creative_links'] = base64_encode($creative['creative_links']);
                }
                
                # get all the columns names 
                $creativesColumns = array('id','creative','status','created_by','created_date','last_updated_by','last_updated_at');

                # set the list into the template data system 
                $this->getPageView()->set('creativesList',$creativesList);

                # set the columns list into the template data system 
                $this->getPageView()->set('creativesColumns',$creativesColumns);

                # get offernames
                $offerNamesList = Database::getCurrentDatabaseConnector()->executeQuery("SELECT onm.id AS id,"
                                                . " onm.value AS value,"
                                                . " st.name AS status,"
                                                . " uc.username AS created_by,"
                                                . " st.created_at AS created_date,"
                                                . " up.username AS last_updated_by,"
                                                . " st.last_updated_at AS last_updated_at"
                                                . " FROM admin.offer_names onm"
                                                . " LEFT JOIN admin.users uc ON uc.id = onm.created_by"
                                                . " LEFT JOIN admin.status st ON st.id = onm.status_id"
                                                . " LEFT JOIN admin.users up ON up.id = onm.last_updated_by"
                                                . " WHERE onm.offer_id = $id"
                                                . " ORDER BY onm.id", true);
                        
                # add index to the creatives
                $index = 1;
                foreach ($offerNamesList as &$offerName) 
                {
                    $offerName['offer_name'] = "offer_name_$index";
                    $index++;
                }
                
                # get all the columns names 
                $offerNamesColumns = array('id','offer_name','value','status','created_by','created_date','last_updated_by','last_updated_at');

                # set the list into the template data system 
                $this->getPageView()->set('offerNamesList',$offerNamesList);

                # set the columns list into the template data system 
                $this->getPageView()->set('offerNamesColumns',$offerNamesColumns);
                
                # get subjects
                $offerSubjectsList = Database::getCurrentDatabaseConnector()->executeQuery("SELECT os.id AS id,"
                                                . " os.value AS value,"
                                                . " st.name AS status,"
                                                . " uc.username AS created_by,"
                                                . " st.created_at AS created_date,"
                                                . " up.username AS last_updated_by,"
                                                . " st.last_updated_at AS last_updated_at"
                                                . " FROM admin.offer_subjects os"
                                                . " LEFT JOIN admin.users uc ON uc.id = os.created_by"
                                                . " LEFT JOIN admin.status st ON st.id = os.status_id"
                                                . " LEFT JOIN admin.users up ON up.id = os.last_updated_by"
                                                . " WHERE os.offer_id = $id"
                                                . " ORDER BY os.id", true);
                        
                # add index to the creatives
                $index = 1;
                foreach ($offerSubjectsList as &$offerSubject) 
                {
                    $offerSubject['offer_subject'] = "offer_subject_$index";
                    $index++;
                }
                
                # get all the columns names 
                $offerSubjectsColumns = array('id','offer_subject','value','status','created_by','created_date','last_updated_by','last_updated_at');

                # set the list into the template data system 
                $this->getPageView()->set('offerSubjectsList',$offerSubjectsList);

                # set the columns list into the template data system 
                $this->getPageView()->set('offerSubjectsColumns',$offerSubjectsColumns);
                
                # set the data to the template
                $this->getPageView()->set('offer',$offer);
                $this->getPageView()->set('sponsors',$sponsors);
                $this->getPageView()->set('verticals',$verticals);
                $this->getPageView()->set('status',$status);
            }
        }
        
        /**
         * @name upload
         * @description the upload action
         * @before init
         * @after setMenu,closeConnection
         */
        public function upload() 
        {
            # get the connected user
            $user = Session::get('mailtng_connected_user'); 
            
            $message = "Incorrect Offer Id or You need to wait for a while and retry !";
            $messageFlag = 'error';
                
            # retrieves the data from post
            $sponsorId = intval(Request::getParameterFromPOST('sponsor-id'));
            $offerIds = explode(PHP_EOL,Request::getParameterFromPOST('production-ids'));
                    
            # check if there is non-numeric values in offer ids 
            $valid = true;

            foreach ($offerIds as &$id)
            {
                $id = preg_replace( "/\r|\n/","", trim($id));

                if(intval($id) == 0)
                {
                    $valid = false;
                    $message = 'Incorrect Offer Id : ' . $id . ' !';
                    break;
                }
            }

            if($sponsorId > 0 && $valid == true)
            {
                $api = null;
                $sponsor = Sponsor::first(true,array('id = ?',$sponsorId));
                    
                if(count($sponsor))
                {
                    $api = Api::getAPIClass($sponsor); 
                    
                    if($api != null)
                    {
                        $offers = $api->getOffers($offerIds);
                        
                        if(count($offers))
                        {
                            foreach ($offers as $offer) 
                            {
                                
                                if(count($offer) && $offer['id'] != null && intval($offer['id']) > 0)
                                {
                                    # get the connected user
                                    $user = Session::get('mailtng_connected_user'); 

                                    # initialize the offer id
                                    $offerId = 0;
                                    
                                    # initialize the offer Object
                                    $offerObject = new Offer();

                                    # if offer by id already there 
                                    $checkOffer = Offer::first(true, array('sponsor_id = ? AND production_id = ?',array($sponsorId,$offer['id'])));

                                    if(count($checkOffer))
                                    {
                                        # delete offer 
                                        $offerId = $checkOffer['id'];
                                        OfferName::deleteAll(array('offer_id = ?',$offerId));
                                        OfferSubject::deleteAll(array('offer_id = ?',$offerId));

                                        # delete offer creatives
                                        $checkCreatives = OfferCreative::all(true,array('offer_id = ?',$offerId));

                                        if(count($checkCreatives))
                                        {
                                            foreach ($checkCreatives as $checkCreative) 
                                            {
                                                if(count($checkCreative))
                                                {
                                                    $id = $checkCreative['id'];
                                                    OfferLink::deleteAll(array('creative_id = ?',$id));
                                                }
                                            }

                                            OfferCreative::deleteAll(array('offer_id = ?',$offerId));
                                        } 
                                        
                                        $offerObject->setId($offerId);
                                        $offerObject->load();
                                    }

                                    $offerObject->setStatus_id(1);
                                    $offerObject->setSponsor_id($sponsorId);
                                    $offerObject->setCampaign_id($offer['campaign-id']);
                                    $offerObject->setProduction_id($offer['id']);

                                    # vertical 
                                    $offerObject->setVertical_id(1);

                                    if($offer['vertical'] != "")
                                    {
                                        $verticalObject = Database::getCurrentDatabaseConnector()->executeQuery("SELECT * FROM admin.verticals WHERE name LIKE '%{$offer['vertical']}%' LIMIT 1",true);

                                        if(count($verticalObject))
                                        {
                                            $offerObject->setVertical_id($verticalObject[0]['id']);
                                        }
                                        else
                                        {
                                            $vertical = new Vertical();
                                            $vertical->setStatus_id(1);
                                            $vertical->setName($offer['vertical']);
                                            $vertical->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                            $vertical->setCreated_at(date("Y-m-d"));
                                            $vertical->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                            $vertical->setLast_updated_at(date("Y-m-d"));

                                            $verticalId = $vertical->save(true); 
                                            
                                            $offerObject->setVertical_id($verticalId);
                                        }
                                    }

                                    $offerObject->setName($offer['name']);
                                    $offerObject->setFlag($offer['flag']);
                                    $offerObject->setDescription($offer['description']);
                                    $offerObject->setRate($offer['rate']);
                                    $offerObject->setLaunch_date($offer['launch-date']);
                                    $offerObject->setExpiring_date($offer['expiring-date']);
                                    $offerObject->setRules($offer['rules']);
                                    $offerObject->setEpc($offer['epc']);
                                    $offerObject->setSuppression_list($offer['suppression-list-link']);
                                    $offerObject->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                    $offerObject->setCreated_at(date("Y-m-d"));
                                    $offerObject->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                    $offerObject->setLast_updated_at(date("Y-m-d"));
                                    
                                    $returnedId = $offerObject->save(true);
                                    
                                    $offerId = $offerId == 0 ? $returnedId : $offerId;

                                    if(count($offer['offer_names']))
                                    {
                                        foreach ($offer['offer_names'] as $value) 
                                        {
                                            if(strlen(trim($value)) > 0)
                                            {
                                                $offerName = new OfferName();
                                                $offerName->setStatus_id(1);
                                                $offerName->setOffer_id($offerId);
                                                $offerName->setValue($value);
                                                $offerName->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                                $offerName->setCreated_at(date("Y-m-d"));
                                                $offerName->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                                $offerName->setLast_updated_at(date("Y-m-d"));
                                                $offerName->save();
                                            }
                                        }
                                    }

                                    if(count($offer['offer_subjects'])) 
                                    {
                                        foreach ($offer['offer_subjects'] as $value) 
                                        {
                                            if(strlen(trim($value)) > 0)
                                            {
                                                $offerSubject = new OfferSubject();
                                                $offerSubject->setStatus_id(1);
                                                $offerSubject->setOffer_id($offerId);
                                                $offerSubject->setValue($value);
                                                $offerSubject->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                                $offerSubject->setCreated_at(date("Y-m-d"));
                                                $offerSubject->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                                $offerSubject->setLast_updated_at(date("Y-m-d"));
                                                $offerSubject->save();
                                            }
                                        }
                                    }

                                    if(count($offer['creatives']))
                                    {
                                        foreach ($offer['creatives'] as $creative) 
                                        {
                                            if(count($creative) && $creative['code'] != "")
                                            {
                                                $offerCreative = new OfferCreative();
                                                $offerCreative->setStatus_id(1);
                                                $offerCreative->setOffer_id($offerId);
                                                $offerCreative->setValue($creative['code']);
                                                $offerCreative->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                                $offerCreative->setCreated_at(date("Y-m-d"));
                                                $offerCreative->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                                $offerCreative->setLast_updated_at(date("Y-m-d"));
                                                $creativeId = $offerCreative->save(true);

                                                if(count($creative['links']))
                                                {
                                                    foreach ($creative['links'] as $rowLink) 
                                                    {
                                                        if(is_array($rowLink) && count($rowLink))
                                                        {
                                                            $link = $rowLink['link'];
                                                            $type = $rowLink['type'];

                                                            $offerLink = new OfferLink();
                                                            $offerLink->setStatus_id(1);
                                                            $offerLink->setCreative_id($creativeId);
                                                            $offerLink->setValue($link);
                                                            $offerLink->setType($type);
                                                            $offerLink->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                                            $offerLink->setCreated_at(date("Y-m-d"));
                                                            $offerLink->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                                            $offerLink->setLast_updated_at(date("Y-m-d"));
                                                            $offerLink->save();   
                                                        }    
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    $message = "Record stored successfully !";
                                    $messageFlag = 'success';
                                }
                            }
                        }
                    }
                }
            }
            
            # stores the message in the session 
            Session::set('proccess_message_flag',$messageFlag);
            Session::set('proccess_message',$message);
            
            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html'); 
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
                Offer::deleteAll(array('id = ?',$id));
                OfferName::deleteAll(array('offer_id = ?',$id));
                OfferSubject::deleteAll(array('offer_id = ?',$id));

                # delete offer creatives
                $checkCreatives = OfferCreative::all(true,array('offer_id = ?',$id));

                if(count($checkCreatives))
                {
                    foreach ($checkCreatives as $checkCreative) 
                    {
                        if(count($checkCreative))
                        {
                            $id = $checkCreative['id'];
                            OfferLink::deleteAll(array('creative_id = ?',$id));
                        }
                    }

                    OfferCreative::deleteAll(array('offer_id = ?',$id));
                } 
                            
                $message = "Record deleted successfully !";
                $messageFlag = 'success';
            }

            # stores the message in the session 
            Session::set('proccess_message_flag',$messageFlag);
            Session::set('proccess_message',$message);

            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
        }
        
        /**
         * @name creatives
         * @description the creatives action
         * @before init
         * @after setMenu,closeConnection
         */
        public function creatives() 
        {
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'edit';

            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_offers',true);
 
                switch ($page) 
                {
                    case 'add' :
                    {
                        $offerId = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        if(isset($offerId) && is_numeric($offerId))
                        {
                            # retrieve data
                            $offer = Offer::first(true,array('id = ?',$offerId));

                            # get status list 
                            $status = Status::all(true,array(),array('id','name'),'id','ASC');

                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'offers' . DS . 'creatives' . DS . 'add' . '.' . $this->getDefaultExtension());

                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_offers_add',true);

                            # set the data to the template
                            $this->getPageView()->set('offer',$offer);
                    
                            # set the list into the template data system 
                            $this->getPageView()->set('status',$status);
                        }
                        break;
                    }
                    case 'edit' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        if(isset($id) && is_numeric($id))
                        {
                            # retrieve data
                            $creative = OfferCreative::first(true,array('id = ?',$id));
                            $links = array();
                            
                            if(count($creative))
                            {
                                $links = OfferLink::all(true, array('creative_id = ?',$creative['id']));
                            }

                            # get status list 
                            $status = Status::all(true,array(),array('id','name'),'id','ASC');

                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'offers' . DS . 'creatives' . DS . 'edit' . '.' . $this->getDefaultExtension());

                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_offers_add',true);

                            # set the data to the template
                            $this->getPageView()->set('creative',$creative);
                            
                            # set the data to the template
                            $this->getPageView()->set('links',$links);

                            # set the list into the template data system 
                            $this->getPageView()->set('status',$status);
                        }
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('mailtng_connected_user'); 

                        # retrieves the data from post
                        $creativeId = Request::getParameterFromPOST('id');
                        $offerId = Request::getParameterFromPOST('offer-id');
                        $creativeHTML = Request::getParameterFromPOST('creative-html');
                        $creativeStatus = Request::getParameterFromPOST('status-id');

                        if(isset($creativeHTML))
                        {
                            $message = "Something went wrong !";
                            $messageFlag = 'error';

                            if($creativeId != NULL && is_numeric($creativeId))
                            {
                                $linksIds = Request::getParameterFromPOST('link-id');
                                $linksTypes = Request::getParameterFromPOST('link-type');
                                $linksValues = Request::getParameterFromPOST('link-value');
                        
                                # update case
                                $creative = new OfferCreative(array("id" => $creativeId));
                                $creative->setStatus_id(intval($creativeStatus));
                                $creative->setValue($creativeHTML);
                                $creative->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $creative->setLast_updated_at(date("Y-m-d"));

                                $result = $creative->save(); 

                                if($result > -1)
                                {
                                    if(count($linksIds) == count($linksTypes) && count($linksTypes) == count($linksValues))
                                    {
                                        foreach ($linksTypes as $key => $type) 
                                        {
                                            $offerLink = new OfferLink(array("id" => $linksIds[$key]));
                                            $offerLink->setStatus_id(intval($creativeStatus));
                                            $offerLink->setValue($linksValues[$key]);
                                            $offerLink->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                            $offerLink->setLast_updated_at(date("Y-m-d"));

                                            if($type == 'delete')
                                            {
                                                $offerLink->delete();
                                            }
                                            else
                                            {
                                                $offerLink->setType($type);
                                                $offerLink->save();
                                            }
                                        }
                                    }
                                    
                                    $message = "Record updated succesfully !";
                                    $messageFlag = 'success';
                                }
                            }
                            else
                            {
                                $previewLink = Request::getParameterFromPOST('preview-link');
                                $unsubLink = Request::getParameterFromPOST('unsub-link');
                        
                                # update case
                                $creative = new OfferCreative();
                                $creative->setStatus_id(intval($creativeStatus));
                                $creative->setOffer_id(intval($offerId));
                                $creative->setValue($creativeHTML);
                                $creative->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                $creative->setCreated_at(date("Y-m-d"));
                                $creative->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $creative->setLast_updated_at(date("Y-m-d"));

                                $creativeId = $creative->save(true); 

                                if($creativeId > -1)
                                {
                                    if(filter_var($previewLink,FILTER_VALIDATE_URL))
                                    {
                                        # add preview link
                                        $offerLink = new OfferLink();
                                        $offerLink->setStatus_id(intval($creativeStatus));
                                        $offerLink->setCreative_id($creativeId);
                                        $offerLink->setType('preview');
                                        $offerLink->setValue($previewLink);
                                        $offerLink->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                        $offerLink->setCreated_at(date("Y-m-d"));
                                        $offerLink->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                        $offerLink->setLast_updated_at(date("Y-m-d"));
                                        $offerLink->save();
                                    }
                                    
                                    if(filter_var($unsubLink,FILTER_VALIDATE_URL))
                                    {
                                        # add unsub link
                                        $offerLink = new OfferLink();
                                        $offerLink->setStatus_id(intval($creativeStatus));
                                        $offerLink->setCreative_id($creativeId);
                                        $offerLink->setType('unsub');
                                        $offerLink->setValue($unsubLink);
                                        $offerLink->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                        $offerLink->setCreated_at(date("Y-m-d"));
                                        $offerLink->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                        $offerLink->setLast_updated_at(date("Y-m-d"));
                                        $offerLink->save();
                                    }
                                    
                                    $message = "Record updated succesfully !";
                                    $messageFlag = 'success';
                                }
                            }
                            
                            # stores the message in the session 
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                        }

                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
                    }
                    case 'delete' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        $message = "Something went wrong !";
                        $messageFlag = 'error';

                        if(isset($id) && is_numeric($id))
                        {
                            # delete the server
                            $creative = new OfferCreative(array("id" => $id));
                            $creative->delete();
                            $message = "Record deleted!";
                            $messageFlag = 'success';
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);
   
                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
                        
                        break;
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
                    }    
                }
            }
        }
        
        /**
         * @name names
         * @description the from names action
         * @before init
         * @after setMenu,closeConnection
         */
        public function names() 
        {
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'add';

            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_offers',true);
 
                switch ($page) 
                {
                    case 'add' :
                    {
                        $offerId = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        if(isset($offerId) && is_numeric($offerId))
                        {
                            # retrieve data
                            $offer = Offer::first(true,array('id = ?',$offerId));
                            
                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'offers' . DS . 'names' . DS . 'add' . '.' . $this->getDefaultExtension());

                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_offers_add',true);

                            # set the data to the template
                            $this->getPageView()->set('offer',$offer);
                        }
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('mailtng_connected_user'); 

                        $message = "Something went wrong !";
                        $messageFlag = 'error';
                            
                        # retrieves the data from post
                        $offerId = Request::getParameterFromPOST('offer-id');
                        $namesList = explode(PHP_EOL,Request::getParameterFromPOST('from-names'));

                        if(is_array($namesList) && count($namesList))
                        {
                            foreach ($namesList as $name) 
                            {
                                $name = preg_replace( "/\r|\n/","",trim($name));
                                
                                if(strlen($name) > 0)
                                {
                                    $offerName = new OfferName();
                                    $offerName->setStatus_id(1);
                                    $offerName->setOffer_id($offerId);
                                    $offerName->setValue($name);
                                    $offerName->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                    $offerName->setCreated_at(date("Y-m-d"));
                                    $offerName->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                    $offerName->setLast_updated_at(date("Y-m-d"));
                                    $offerName->save();
                                }
                            }
                            
                            $message = "Records stored succesfully !";
                            $messageFlag = 'success';
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);
                            
                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
                    }    
                }
            }
        }
        
        /**
         * @name subjects
         * @description the subjects action
         * @before init
         * @after setMenu,closeConnection
         */
        public function subjects() 
        {
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'add';

            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_offers',true);
 
                switch ($page) 
                {
                    case 'add' :
                    {
                        $offerId = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        if(isset($offerId) && is_numeric($offerId))
                        {
                            # retrieve data
                            $offer = Offer::first(true,array('id = ?',$offerId));
                            
                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'offers' . DS . 'subjects' . DS . 'add' . '.' . $this->getDefaultExtension());

                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_offers_add',true);

                            # set the data to the template
                            $this->getPageView()->set('offer',$offer);
                        }
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('mailtng_connected_user'); 

                        $message = "Something went wrong !";
                        $messageFlag = 'error';
                            
                        # retrieves the data from post
                        $offerId = Request::getParameterFromPOST('offer-id');
                        $subjectsList = explode(PHP_EOL,Request::getParameterFromPOST('subjects'));

                        if(is_array($subjectsList) && count($subjectsList))
                        {
                            foreach ($subjectsList as $subject) 
                            {
                                $subject = preg_replace( "/\r|\n/","",trim($subject));
                                
                                if(strlen($subject) > 0)
                                {
                                    $offerSubject = new OfferSubject();
                                    $offerSubject->setStatus_id(1);
                                    $offerSubject->setOffer_id($offerId);
                                    $offerSubject->setValue($subject);
                                    $offerSubject->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                    $offerSubject->setCreated_at(date("Y-m-d"));
                                    $offerSubject->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                    $offerSubject->setLast_updated_at(date("Y-m-d"));
                                    $offerSubject->save();
                                }
                            }
                            
                            $message = "Records stored succesfully !";
                            $messageFlag = 'success';
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);
                            
                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
                    }    
                }
            }
        }
        
        /**
         * @name getCreative
         * @description get creative by id
         * @before init
         * @after setMenu,closeConnection
         */
        public function getCreative() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                if($this->getDefaultContentType() == 'text/json')
                { 
                    $creativeId = intval(Request::getParameterFromPOST('creative-id'));

                    if(isset($creativeId) && is_numeric($creativeId) && $this->getDefaultContentType() == 'text/json')
                    {
                        $content = '';
                        
                        $creative = OfferCreative::first(true, array('id = ?',$creativeId)); 

                        if(count($creative))
                        {
                            $content = $creative['value'];
                        }
                        
                        die(json_encode(array( "creative" => $content)));                        
                    }
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'offers' . RDS . 'lists.html');
            }
        }
        
        /**
         * @name uploadimages
         * @description the upload images action
         * @before init
         * @after setMenu,closeConnection
         */
        public function images() 
        {
            
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'add';

            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_images',true);
 
                switch ($page) 
                {
                    case 'add' :
                    {
                        # retrieve data
                        $servers = Server::all(true,array('status_id = ?',1),array('id','name'),'id','ASC');

                        # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'offers' . DS . 'images' . DS . 'add' . '.' . $this->getDefaultExtension());

                        # set the menu item to active 
                        $this->getMasterView()->set('menu_admin_images_add',true);

                        # set the data to the template
                        $this->getPageView()->set('servers',$servers);
                        
                        break;
                    }
                    case 'save' :
                    {
                        $message = "Something went wrong !";
                        $messageFlag = 'error';
                        $target_file = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . 'uploads' . DS;
                        
                        $serverIDs = Request::getParameterFromPOST('server-ids');
                        $images = Request::getParameterFromFILES("imagesToUpload");
                        $imageName = \ma\mailtng\types\Strings::generateRandomText(3,true,true,true,false).'_'.$images['name'];
                        $target_file = $target_file . basename($imageName);
                        $extension = pathinfo($target_file, PATHINFO_EXTENSION);
                        move_uploaded_file($images['tmp_name'], $target_file);
                        # upload the image 
                        $content = file_get_contents($target_file);
                        if(is_array($serverIDs) && count($serverIDs))
                        {
                            foreach ($serverIDs as $serverID)
                            {
                                $Server = Server::first(true, array('id = ?',$serverID));
                                $redirectImagesLink = 'http://[RDNS]' . RDS . 'web' . RDS . 'imgs' . RDS;
                                
                                $sshAuthenticator = new SSHPasswordAuthentication($Server['username'],$Server['password']);
                                $sshConnector = new SSH($Server['main_ip'],$sshAuthenticator,$Server['ssh_port']);
                                
                                if($sshConnector->isConnected())
                                {
                                    $sshConnector->scp('send',array('/var/mailtng/web/imgs/' . $imageName . '.' . $extension),$content);
                                    $imageLink = $redirectImagesLink . $imageName . '.' . $extension;
                                    \ma\mailtng\output\PrintWriter::printValue($imageLink);
                                }
                                else
                                {
                                    \ma\mailtng\output\PrintWriter::printValue("Error while Uploading the image on server . ".$Server['name']);
                                    
                                }
                                
                            }
                        }
                        // Upload on all Servers ...
                        else
                        {
                            $Servers = Server::all(true,array('status_id = ?',1));
                            
                            $redirectImagesLink = 'http://[RDNS]' . RDS . 'web' . RDS . 'imgs' . RDS;

                            foreach ($Servers as $Server)
                            {
                                
                                $sshAuthenticator = new SSHPasswordAuthentication($Server['username'],$Server['password']);
                                $sshConnector = new SSH($Server['main_ip'],$sshAuthenticator,$Server['ssh_port']);
                                
                                if($sshConnector->isConnected())
                                {
                                    $sshConnector->scp('send',array('/var/mailtng/web/imgs/' . $imageName . '.' . $extension),$content);
                                }
                                else
                                {
                                    \ma\mailtng\output\PrintWriter::printValue("Error while Uploading the image on server . ".$Server['name']);
                                    
                                }
                            }
                            $imageLink = $redirectImagesLink . $imageName . '.' . $extension;

                            \ma\mailtng\output\PrintWriter::printValue($imageLink);
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);
                            
                        # redirect to show list 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'offers' . DS . 'images' . DS . 'lists' . '.' . $this->getDefaultExtension());
                    }
                    default:
                    {
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'offers' . DS . 'images' . DS . 'lists' . '.' . $this->getDefaultExtension());
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