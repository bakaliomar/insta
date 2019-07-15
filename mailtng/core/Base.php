<?php namespace ma\mailtng\core
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
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\metadata\Inspector as Inspector;
    use ma\mailtng\exceptions\types\BackendException as BackendException;
    /**
     * @name            Base.class 
     * @description     Contains magic getters and setters methods that sub classes will work with
     * @package		ma\mailtng\core
     * @category        Base Class
     * @author		MailTng Team			
     */
    class Base 
    {
        /**
         * @name __construct
         * @description the class constructor
         * @access public
         * @param array $options
         * @return Base
         */
        public function __construct($options = array()) 
        {
            if (is_array($options) || is_object($options)) 
            {
                foreach ($options as $key => $value) 
                {
                    $key = ucfirst($key);
                    $method = "set{$key}";
                    $this->$method($value);
                }
            }
        }

        /**
         * @name __call
         * @description plays the role of a generic getter and setter
         * @access public
         * @param array $method
         * @param array $arguments
         * @return mixed
         * @throws BackendException
         */
        public function __call($method, $arguments)
        {   
            # getting the subclass name
            $class = get_called_class();
                   
            # getters case
            $getMatches = Strings::match($method, "^get([a-zA-Z0-9_]+)$");
            
            if (sizeof($getMatches) > 0) 
            {
                $normalized = lcfirst($getMatches[0]);
                $property = "_{$normalized}";
                
                if (property_exists($class, $property)) 
                {
                    $meta = Inspector::getPropertyMetaData($class,$property);   
                    
                    if (empty($meta["@readwrite"]) && empty($meta["@read"])) 
                    {
                        throw new BackendException("You cannot get this property it's not readable");
                    }
                    
                    if (isset($this->$property)) 
                    {
                        return $this->$property;
                    }
                    else
                    {
                        return null;
                    } 
                }
            }
            
            # setters case
            $setMatches = Strings::match($method, "^set([a-zA-Z0-9_]+)$");
            
            if (sizeof($setMatches) > 0) 
            {
                $normalized = lcfirst($setMatches[0]);
                $property = "_{$normalized}";
                
                if (property_exists($class, $property)) 
                {
                    $meta = Inspector::getPropertyMetaData($class,$property);
                    
                    if (empty($meta["@readwrite"]) && empty($meta["@write"])) 
                    {
                        throw new BackendException("You cannot get this property it's not readonly");
                    }
                    
                    $this->$property = $arguments[0];
                    return true;
                }
            }

            $class = (get_called_class() != "" && get_called_class() != false) ? "in " . get_called_class() : "";
            
            throw new BackendException("{$method} method is not implemented " .$class );
        }

        /**
         * @name __get
         * @description plays the role of a generic getter
         * @access public
         * @param array $name
         * @return mixed
         */
        public function __get($name) 
        {
            $function = "get" . ucfirst(trim(trim($name,'_')));
            return $this->$function();
        }
        
        /**
         * @name __set
         * @description plays the role of a generic setter
         * @access public
         * @param array $name
         * @param mixed $value
         * @return
         */
        public function __set($name, $value) 
        {
            $function = "set" . ucfirst(trim(trim($name,'_')));
            return $this->$function($value);
        }
    }
}
