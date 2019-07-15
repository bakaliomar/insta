<?php namespace ma\mailtng\caching\drivers
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
    use ma\mailtng\caching\Driver as Driver;
    use ma\mailtng\application\Application as Application;
    use ma\mailtng\exceptions\types\CacheException as CacheException;
    /**
     * @name            FileSystem.class 
     * @description     It's a chache driver that deals with caching in files inside the framework
     * @package		ma\mailtng\caching\drivers
     * @category        Caching Class
     * @author		MailTng Team			
     */
    class FileSystem extends Driver
    {      
        /**
         * @readwrite
         * @access protected 
         * @var integer
         */
        protected $_expiration = 3600;
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_type;
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_folder;
  
        /**
         * @name __construct
         * @description the class constructor
         * @access public
         * @param array $options
         * @return FileSystem
         */
        public function __construct($options = array()) 
        {
            parent::__construct($options); 
            $this->_folder = APPS_FOLDER. DS . Application::getPrefix() . DS . DEFAULT_TEMP_DIRECTORY . DS . DEFAULT_CACHE_DIRECTORY;
        }

        /**
         * @name get
         * @description retrieves a value from the cache system
         * @access public
         * @param string $key
         * @param mixed $default
         * @return mixed
         * @throws CacheException
         */
        public function get($key, $default = null) 
        {
            $filename = $this->_folder . DS . sha1($key) . '.cache';
            if(file_exists($filename))
            {
                if ((time()-filemtime($filename) <= $this->_expiration)) 
                {
                    if (!$data = file_get_contents($filename)) 
                    {
                        throw new CacheException('Error reading data from cache file');
                    } 
                    return unserialize(base64_decode($data));
                } 
                else 
                {
                    throw new CacheException('You have exceeded the expiration time of a cache file');
                }
            }
            return $default;
        }

        /**
         * @name set
         * @description stores a value into the cache system
         * @access public
         * @param string $key
         * @param mixed $value
         * @return 
         * @throws CacheException
         */
        public function set($key, $value)
        {
            $filename = $this->_folder . DS . sha1($key) . '.cache';
            if (file_exists($filename)) 
            {
                unlink($filename);
            }
            if (!file_put_contents($filename, base64_encode(serialize($value)))) 
            {
                throw new CacheException('Error writing data to cache file');
            }
            return true;
        }

        /**
         * @name erase
         * @description deletes a value from the cache system
         * @access public
         * @param string $key
         * @return 
         * @throws CacheException
         */
        public function erase($key) 
        {
            $file = $this->_folder . DS . sha1($key) . '.cache';
            if (file_exists($file)) 
            {
                return unlink($file);
            }
            return true;
        }
    }
}