<?php namespace ma\mailtng\caching
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
    use ma\mailtng\caching\drivers\Memcached as Memcached;
    use ma\mailtng\caching\drivers\FileSystem as FileSystem;
    use ma\mailtng\types\Objects as Objects;
    use ma\mailtng\exceptions\types\CacheException as CacheException;
    /**
     * @name            Cache.class 
     * @description     The main cache class that initialize the cache driver based on the type 
     * @package		ma\mailtng\caching
     * @category        Caching Class
     * @author		MailTng Team			
     */
    class Cache extends Base
    { 
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_type;

        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_options;        

        /**
         * @name initialize
         * @description initializes the cache driver based on the given type 
         * @access public
         * @return mixed
         * @throws CacheException
         */
        public function initialize() 
        {
            $type = $this->getType();
            if (empty($type)) 
            {
                $configuration = Packager::get("configuration");
                if ($configuration) 
                {
                    $parsed = $configuration->parse(ROOT_PATH . DS . FW_FOLDER . DS . DEFAULT_CONFIGS_DIRECTORY . DS . "cache");
                    
                    if (!empty($parsed->cache->default) && !empty($parsed->cache->default->type)) 
                    {
                        $type = $parsed->cache->default->type;
                        unset($parsed->cache->default->type);
                        $this->__construct(array(
                            "type" => $type,
                            "options" => Objects::objectToArray($parsed->cache->default)
                        ));
                    }
                }
            }

            if (!$this->type) 
            {
                throw new CacheException("Invalid type");
            }
            switch ($this->type) 
            {
                case "memcached": 
                {
                    return new Memcached($this->options);
                }
                case "file": 
                {
                    return new FileSystem($this->options);
                }
                default: 
                {
                    throw new CacheException("Invalid type");
                }
            }
        }

        /**
         * @name getFromCache
         * @description gets a value from the cache that is stored in the packager
         * @access static
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function getFromCache($key,$default = null) 
        {
            if(isset($key))
            {
                $cacheManager = Packager::get('cache');
                if(isset($cacheManager))
                {
                    try 
                    {
                        return $cacheManager->get($key);
                    } 
                    catch (CacheException $exc) 
                    {
                        return $default;
                    }
                }
            }
            return $default;
        }

        /**
         * @name storeInCache
         * @description stores a value in the cache that is stored in the packager
         * @access static
         * @param string $key
         * @param mixed $value
         * @return
         */
        public static function storeInCache($key,$value) 
        {
            if(isset($key))
            {
                $cacheManager = Packager::get('cache');
                if(isset($cacheManager))
                {
                    $cacheManager->set($key,$value);
                }
            }
        }

        /**
         * @name eraseFromCache
         * @description erase a value from the cache that is stored in the packager
         * @access static
         * @param string $key
         * @return
         */
        public static function eraseFromCache($key) 
        {
            if(isset($key))
            {
                $cacheManager = Packager::get('cache');
                if(isset($cacheManager))
                {
                    $cacheManager->erase($key);
                }
            }
        }
    }
}