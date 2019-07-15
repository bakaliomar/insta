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
    use ma\mailtng\exceptions\types\CacheException as CacheException;
    /**
     * @name            Memcached.class 
     * @description     It's a chache driver that deals with caching in files inside the memcached server
     * @package		ma\mailtng\caching\drivers
     * @category        Caching Class
     * @author		MailTng Team			
     */
    class Memcached extends Driver
    {  
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_service;

        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_host = "127.0.0.1";

        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_port = "11211";
        
        /**
         * @readwrite
         * @access protected 
         * @var boolean
         */
        protected $_isConnected = false;
        
        /**
         * @name _isValidService
         * @description returns if it's a valid Memcached service and it's connected
         * @access protected
         * @return boolean
         */
        protected function _isValidService() 
        {
            $isEmpty = empty($this->_service);
            $isInstance = $this->_service instanceof \Memcache;
            if ($this->isConnected && $isInstance && !$isEmpty) 
            {
                return true;
            }
            return false;
        }

        /**
         * @name connect
         * @description connect to Memcached server
         * @access public
         * @return Memcached
         */
        public function connect() 
        {
            try 
            {
                $this->_service = new \Memcache();
                $this->_service->connect($this->host, $this->port);
                $this->isConnected = true;
            } 
            catch (\Exception $exception) 
            {
                throw new CacheException("Unable to connect to service",500,$exception);
            }
            return $this;
        }

        /**
         * @name disconnect
         * @description disconnect from Memcached server
         * @access protected
         * @return Memcached
         */
        public function disconnect() 
        {
            if ($this->_isValidService()) 
            {
                $this->_service->close();
                $this->isConnected = false;
            }
            return $this;
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
            if (!$this->_isValidService()) 
            {
                throw new CacheException("Not connected to a valid service");
            }
            
            $value = $this->_service->get(sha1($key), MEMCACHE_COMPRESSED);
            if ($value) 
            {
                return $value;
            }

            return $default;
        }

        
        /**
         * @name set
         * @description stores a value into the cache system
         * @access public
         * @param string $key
         * @param mixed $value
         * @param integer $duration
         * @return Memcached
         * @throws CacheException
         */
        public function set($key, $value, $duration = 120) 
        {
            if (!$this->_isValidService()) 
            {
                throw new CacheException("Not connected to a valid service");
            }
            $this->_service->set(sha1($key), $value, MEMCACHE_COMPRESSED, $duration);
            return $this;
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
            if (!$this->_isValidService()) 
            {
                throw new CacheException("Not connected to a valid service");
            }
            $this->_service->delete(sha1($key));
            return $this;
        }
    }
}