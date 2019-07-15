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
    use ma\mailtng\templating\Template as Template;
    use ma\mailtng\templating\implementations\Extended as ExtendedImplementation;
    use ma\mailtng\exceptions\types\ArgumentException as ArgumentException;
    /**
     * @name            View.class
     * @description     The mother class of all the upcomming views 
     * @package		ma\mailtng\application
     * @category        Core Class
     * @author		MailTng Team			
     */
    class View extends Base
    {
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_file;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_template;

        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_data = array();

        /**
         * @name __construct
         * @description the class constructor
         * @access public
         * @param array $options
         * @return View
         */
        public function __construct($options = array())
        {
            parent::__construct($options);
            $this->_template = new Template(array(
                "implementation" => new ExtendedImplementation()
            ));
        }

        /**
         * @name render
         * @description parses the template , replaces the tags and return the final page
         * @access public
         * @param array $options
         * @return string
         */
        public function render() 
        {
            if (!file_exists($this->getFile())) 
            {
                return "";
            }
            
            $content = file_get_contents($this->getFile());
            $this->_template->parse($content);
            return $this->_template->process($this->_data);
        }

        /**
         * @name get
         * @description retrieves data form the template data array
         * @access public
         * @param string $key
         * @param string $default
         * @return mixed
         */
        public function get($key, $default = "") 
        {
            if (isset($this->_data[$key])) 
            {
                return $this->_data[$key];
            }
            return $default;
        }

        /**
         * @name _set
         * @description stores data inside the template data array
         * @access protected
         * @param string $key
         * @param string $value
         * @return
         * @throws ArgumentException
         */
        protected function _set($key, $value) 
        {
            if (!is_string($key) && !is_numeric($key)) 
            {
                throw new ArgumentException("Key must be a string or a number");
            }
            
            $this->_data[$key] = $value;
        }

        /**
         * @name set
         * @description A public representation of _set method that can take an array as the first argument , in that case we loop throw that array and stores every row of it Note : it should be an associative array
         * @access public
         * @param string $key
         * @param string $value
         * @return View
         * @throws ArgumentException
         */
        public function set($key, $value = null) 
        {
            if (is_array($key)) 
            {
                foreach ($key as $_key => $value) 
                {
                    $this->_set($_key, $value);
                }
                return $this;
            }
            
            $this->_set($key, $value);
            return $this;
        }

        /**
         * @name erase
         * @description erases values from the template data array
         * @access public
         * @param string $key
         * @return View
         */
        public function erase($key) 
        {
            unset($this->_data[$key]);
            return $this;
        }
    }
}