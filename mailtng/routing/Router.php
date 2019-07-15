<?php namespace ma\mailtng\routing
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
    use ma\mailtng\metadata\Inspector as Inspector;
    use ma\mailtng\application\Application as Application;
    use ma\mailtng\exceptions\types\ApplicationException as ApplicationException;
    /**
     * @name            Router.class 
     * @description     It's a class tha routing the url and dispatching to the appropriate controller
     * @package		ma\mailtng\routing
     * @category        Routing Class
     * @author		MailTng Team			
     */
    class Router extends Base 
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_url;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_extension;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_controller;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_action;
        
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_routes = array();

        /**
         * @name addRoute
         * @description adds a route to the predefined routes array
         * @access public
         * @param string $route
         * @return Router
         */
        public function addRoute($route) 
        {
            $this->_routes[] = $route;
            return $this;
        }

        /**
         * @name removeRoute
         * @description removes a route from the predefined ones
         * @access public
         * @param string $route
         * @return Router
         */
        public function removeRoute($route) 
        {
            foreach ($this->_routes as $i => $stored) 
            {
                if ($stored == $route) 
                {
                    unset($this->_routes[$i]);
                }
            }
            return $this;
        }

        /**
         * @name getRoutes
         * @description gets all routes that are predefined
         * @access public
         * @return array
         */
        public function getRoutes() 
        {
            $list = array();
            foreach ($this->_routes as $route) 
            {
                $list[$route->pattern] = get_class($route);
            }
            return $list;
        }

        /**
         * @name dispatch
         * @description dispatches the request 
         * @access public
         * @return
         */
        public function dispatch() 
        {
            $url = $this->url;
            $parameters = array();
            $controller = DEFAULT_CONTROLLER;
            $action = DEFAULT_ACTION;

            foreach ($this->_routes as $route) 
            {
                $matches = $route->matches($url);
                
                if ($matches) 
                {
                    $controller = $route->controller;
                    $action = $route->action;
                    $parameters = $route->parameters;

                    $this->pass($controller, $action, $parameters);
                    return;
                }
            }
            
            $parts = explode("/", trim($url, "/"));

            if (sizeof($parts) > 0) 
            {
                $index = 2;
                
                # default application case
                if(defined('DEFAULT_APPLICATION_PREFIX') && DEFAULT_APPLICATION_PREFIX != '')
                {
                    $controller = $parts[0];  
                }
                else
                {
                    if(count($parts) > 1)
                    {
                        $controller = $parts[1];
                        $index = 3;
                    }
                }
                
                if (sizeof($parts) >= $index) 
                {
                    $action = $parts[$index-1];
                    $parameters = array_slice($parts, $index);
                }
            }

            $this->pass($controller, $action, $parameters);
        }

        /**
         * @name pass
         * @description gets the controller name , action name and loads the appropriate controller object and call the action method , It calls also hooks methods if defined 
         * @access public
         * @param string $controller
         * @param string $action
         * @param array $parameters
         * @return
         * @throws ApplicationException
         */
        protected function pass($controller, $action, $parameters = array()) 
        {     
            $this->_controller = $controller;
            $this->_action = $action;
            $class = FW_VENDOR . ANS . DEFAULT_APPS_DIRECTORY . ANS . Application::getPrefix() . ANS . DEFAULT_CONTROLLERS_DIRECTORY . ANS . ucfirst($controller); 
            
            # check if the controller exists
            if(!file_exists(ROOT_PATH . DS . DEFAULT_APPS_DIRECTORY . DS . Application::getPrefix() . DS . DEFAULT_CONTROLLERS_DIRECTORY . DS . ucfirst($controller) . ".php"))
            {
                throw new ApplicationException("Page Not Found",404);
            }

            # loading the controller
            $instance = new $class(array("parameters" => $parameters));
            $instance->setDefaultExtension($this->_extension);
            $instance->setDefaultContentType("text/".$this->_extension);
            Packager::set("controller", $instance);

            if (!method_exists($instance,$action)) 
            {
                $instance->setShowMasterView(false);
                $instance->setShowPageView(false);
                throw new ApplicationException("Action {$action} not found",404);
            }

            $methodMeta = Inspector::getMethodMetaData($class,$action);

            if (!empty($methodMeta["@protected"]) || !empty($methodMeta["@private"])) 
            {
                throw new ApplicationException("Action {$action} not accessible from routing");
            }

            $hooks = function($meta, $type) use ($class,$instance) 
            {
                if (isset($meta[$type])) 
                {
                    $run = array();

                    foreach ($meta[$type] as $method) 
                    {
                        $hookMeta = Inspector::getMethodMetaData($class,$method);

                        if (in_array($method, $run) && !empty($hookMeta["@once"])) 
                        {
                            continue;
                        }

                        $instance->$method();
                        $run[] = $method;
                    }
                }
            };
            
            # calling "before" hook function
            $hooks($methodMeta, "@before");
            
            # executing the main action requested from the url 
            call_user_func_array(array($instance,$action), is_array($parameters) ? $parameters : array());
            
            # calling "after" hook function
            $hooks($methodMeta, "@after");
        }
    }
}