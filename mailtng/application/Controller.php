<?php namespace ma\mailtng\application
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
    use ma\mailtng\core\Base as Base;
    use ma\mailtng\registry\Packager as Packager;
    use ma\mailtng\www\URL as URL;
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\types\Objects as Objects;
    use ma\mailtng\exceptions\types\ApplicationLayoutException as ApplicationLayoutException;
    /**
     * @name            Controller.class 
     * @description     The mother class of all the upcomming controllers
     * @package		ma\mailtng\application
     * @category        Core Class
     * @author		MailTng Team			
     */
    class Controller extends Base
    {
        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_parameters;

        /**
         * @readwrite
         * @access protected 
         * @var View
         */
        protected $_masterView;

        /**
         * @readwrite
         * @access protected 
         * @var View
         */
        protected $_pageView;

        /**
         * @readwrite
         * @access protected 
         * @var boolean
         */
        protected $_showMasterView = true;

        /**
         * @readwrite
         * @access protected 
         * @var boolean
         */
        protected $_showPageView = true;

        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_templatesPath;

        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_masterTemplate = "master";

        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_defaultExtension = "html";

        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_defaultContentType = "text/html";

        /**
         * @name __construct
         * @description the class constructor
         * @access public
         * @param array $options
         * @return Controller
         */
        public function __construct($options = array()) 
        {
            parent::__construct($options);
            
            $this->_templatesPath = DEFAULT_VIEWS_DIRECTORY;
            
            if ($this->getShowMasterView()) 
            {
                # Master view
                $masterPage = $this->getMasterTemplate();
                $defaultExtension = $this->getDefaultExtension();
                
                $view = new View(array(
                    "file" => APPS_FOLDER. DS . Application::getPrefix() . DS . $this->_templatesPath . DS . $masterPage .  '.' . $defaultExtension
                ));
                
                $this->fillViewWithDefaultData($view);
                $this->setMasterView($view);
            }

            if ($this->getShowPageView()) 
            {
                # Page view
                $router = Packager::get("router");
                
                if($router != null)
                {
                    $controller = $router->getController();
                
                    $action = $router->getAction();
                    $view = new View(array(
                        "file" => APPS_FOLDER. DS . Application::getPrefix() . DS . $this->_templatesPath . DS . $controller . DS . $action . '.' . $defaultExtension
                    ));  

                    $this->fillViewWithDefaultData($view);
                    $this->setPageView($view);
                }
            }
        }
        
        /**
         * @name render
         * @description an automatic view rendering, will first render the current action’s view, and then the layout’s view
         * @access public
         * @return
         * @throws ApplicationLayoutException
         */
        public function render() 
        {
            $results = '';
            
            $defaultContentType = $this->getDefaultContentType();
            
            # check if we have to render the master view and the page view
            $showMasterView = $this->getShowMasterView() && $this->getMasterView();
            $showPageView = $this->getShowPageView() && $this->getPageView();

            try 
            {
                # master view case
                if($showMasterView)
                {
                    $view = $this->getMasterView();
                    
                    # check if there is a page view or not
                    if($showPageView && $this->getPageView() != null)
                    {
                        $view->set("template",$this->getPageView()->render());
                    }
                    
                    $results = $view->render();
                }
                else 
                {
                    if($showPageView)
                    {
                        $view = $this->getPageView();
                        $results = $view->render();
                    }
                }    
                # render the result 
                header("Content-type: {$defaultContentType}");
                echo $results;
                
                # prevent multiple pages shown
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
            } 
            catch (\Exception $exception) 
            {
                throw new ApplicationLayoutException($exception->getMessage(),500,$exception);
            }
        }

        /**
         * @name fillViewWithDefaultData
         * @description fills a view with default data ( application info , user info ..... )
         * @access protected
         * @return
         */
        protected function fillViewWithDefaultData(&$view)
        {
            # retrieving the application
            $application = Packager::get('application');
            
            # retrieving the router
            $router = Packager::get('router');
            
            # filling the view
            if(isset($router) && $router instanceof \ma\mailtng\routing\Router)
            {
                $routerSetting = array(
                    "url" => $router->getUrl(),
                    "controller" => $router->getController(),
                    "action" => $router->getAction()
                );
                $view->set('router',$routerSetting);
            }
            
            if(isset($application) && $application instanceof Application)
            {
                $settings = $application->getSetting('init');
                $settings->base_url = URL::getBaseURL();
                $settings->app_url = URL::getCurrentApplicationURL();
                $settings->skin_url = URL::getCurrentApplicationSkinURL();
                $view->set('app',Objects::objectToArray($settings));                
            }

            # check if there is some user info in the session
            $user = Session::get('mailtng_connected_user');
            if(isset($user))
            {
                $view->set('connectedUser',$user);
            }
        }
        
        /**
         * @name __destruct
         * @description a deconstructor that will call the render method automatiqualy
         * @access protected
         * @return
         */
        public function __destruct()
        {
            $errorArray = error_get_last();
            
            # If there is no error then render the page
            if (!$errorArray) 
            {
               $this->render();
            }
        }
    }
}