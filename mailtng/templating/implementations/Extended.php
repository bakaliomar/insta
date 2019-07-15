<?php namespace ma\mailtng\templating\implementations
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
    use ma\mailtng\registry\Packager as Packager;
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\globals\Server as Server;
    use ma\mailtng\templating\Template as Template;
    use ma\mailtng\application\Application as Application;
    use ma\mailtng\templating\implementations\Standard as StandardImplementation;
    /**
     * @name            Extended.class 
     * @description     An extended implementation of our templates 
     * @package		ma\mailtng\templating\implementations
     * @category        Templating Class
     * @author		MailTng Team			
     */
    class Extended extends StandardImplementation
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_defaultPath;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_defaultKey = "_data";

        /** 
         * @readwrite
         * @access protected 
         * @var integer
         */
        protected $_index = 0;

        /**
         * @name __construct
         * @description the class constructor
         * @access public
         * @param string $options
         * @return Extended
         */
        public function __construct($options = array()) 
        {
            parent::__construct($options);
            
            $this->_defaultPath = DEFAULT_VIEWS_DIRECTORY;
            
            $this->_map = array(
                "partial" => array(
                    "opener" => "{partial",
                    "closer" => "}",
                    "handler" => "_partial"
                ),
                "include" => array(
                    "opener" => "{include",
                    "closer" => "}",
                    "handler" => "_include"
                ),
                "yield" => array(
                    "opener" => "{yield",
                    "closer" => "}",
                    "handler" => "_yield"
                )
            ) + $this->_map;

            $this->_map["statement"]["tags"] = array(
                "set" => array(
                    "isolated" => false,
                    "arguments" => "{key}",
                    "handler" => "set"
                ),
                "append" => array(
                    "isolated" => false,
                    "arguments" => "{key}",
                    "handler" => "append"
                ),
                "prepend" => array(
                    "isolated" => false,
                    "arguments" => "{key}",
                    "handler" => "prepend"
                )
            ) + $this->_map["statement"]["tags"];
        }
        
        /**
         * @name set
         * @description 
         * @access public
         * @param string $key
         * @param string $value
         * @return
         */
        public function set($key, $value) 
        {
            if (StringMethods::indexOf($value, "\$_text") > -1) 
            {
                $first = Strings::indexOf($value, "\"");
                $last = Strings::lastIndexOf($value, "\"");
                $value = stripslashes(substr($value, $first + 1, ($last - $first) - 1));
            }

            if (is_array($key)) 
            {
                $key = $this->_getKey($key);
            }

            $this->_setValue($key, $value);
        }
        
        /**
         * @name append
         * @description 
         * @access public
         * @param string $key
         * @param string $value
         * @return
         */
        public function append($key, $value)
        {
            if (is_array($key))
            {
                $key = $this->_getKey($key);
            }

            $previous = $this->_getValue($key);
            $this->set($key, $previous.$value);
        }

        /**
         * @name prepend
         * @description 
         * @access public
         * @param string $key
         * @param string $value
         * @return
         */
        public function prepend($key, $value)
        {
            if (is_array($key))
            {
                $key = $this->_getKey($key);
            }

            $previous = $this->_getValue($key);
            $this->set($key, $value.$previous);
        }

        /**
         * @name _yield
         * @description 
         * @access protected
         * @param array $tree
         * @param string $content
         * @return
         */
        public function _yield($tree, $content)
        {
            $key = trim($tree["raw"]);
            $value = addslashes($this->_getValue($key));
            return "\$_text[] = \"{$value}\";";
        }
        
        /**
         * @name _getKey
         * @description 
         * @access protected
         * @param array $tree
         * @return
         */
        protected function _getKey($tree) 
        {
            if (empty($tree["arguments"]["key"])) 
            {
                return null;
            }

            return trim($tree["arguments"]["key"]);
        }

        /**
         * @name _setValue
         * @description 
         * @access protected
         * @param array $key
         * @param string $value
         * @return
         */
        protected function _setValue($key, $value) 
        {
            if (!empty($key)) 
            {
                $default = $this->getDefaultKey();

                $data = Packager::get($default, array());
                $data[$key] = $value;

                Packager::set($default, $data);
            }
        }

        /**
         * @name _getValue
         * @description 
         * @access protected
         * @param array $key
         * @return string
         */
        protected function _getValue($key) 
        {
            $data = Packager::get($this->getDefaultKey());

            if (isset($data[$key])) 
            {
                return $data[$key];
            }

            return "";
        }
 
        /**
         * @name _include
         * @description 
         * @access protected
         * @param array $tree
         * @param array $content
         * @return string
         */
        protected function _include($tree, $content) 
        {
            $includesPath = APPS_FOLDER. DS . Application::getPrefix() . DS  . $this->getDefaultPath() . DS;  
            
            $file = trim($tree["raw"]);
            $router = Packager::get('router');
            
            if(isset($router))
            {
                $file = str_replace('$__page',$router->getController(), $file);
            }
            
            $finalFilePath = $includesPath . $file;
            
            if(file_exists($finalFilePath))
            {
                $template = new Template(array(
                    "implementation" => new self()
                ));
                
                if(count(file($finalFilePath,FILE_IGNORE_NEW_LINES)))
                {
                    $content = file_get_contents($finalFilePath);
                    $template->parse($content);
                    $index = $this->_index++;
                    return "function anon_{$index}(\$_data){" . $template->getCode() . "};\$_text[] = anon_{$index}(\$_data);";
                }  
            }
            return '';            
        }
        
        /**
         * @name _partial
         * @description 
         * @access protected
         * @param array $tree
         * @return string
         */
        protected function _partial($tree)
        {
            $address = trim($tree["raw"], " /");
            if (Strings::indexOf($address, "http") != 0)
            {
                $host = Server::get("HTTP_HOST");
                $address = "http://{$host}/{$address}";
            }
            $response = Request::getURLContents(trim($address));
            return "\$_text[] = \"{$response}\";";
        }
    }
}