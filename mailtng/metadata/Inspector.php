<?php namespace ma\mailtng\metadata
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
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\types\Arrays as Arrays; 
    /**
     * @name            Inspector.class 
     * @description     It deals with prologues metadata of methods and properties
     * @package		ma\mailtng\metadata
     * @category        Metadata Class
     * @author		MailTng Team			
     */
    class Inspector extends Base
    {
        /**
         * @name getClassMetaData
         * @description retrieves class metadata
         * @access static
         * @param string $class 
         * @param string $property 
         * @return array
         */
        public static function getClassMetaData($class,$property) 
        {
            $reflection = new \ReflectionClass($class,$property);
            $comment = $reflection->getDocComment();
            
            if (!empty($comment)) 
            {
                $metadata = self::_parse($comment);
            } 
            else 
            {
                $metadata = null;
            }
            
            return $metadata;
        }

        /**
         * @name getMethodMetaData
         * @description retrieves method metadata
         * @access static
         * @param string $class 
         * @param string $method
         * @return array
         */
        public static function getMethodMetaData($class,$method) 
        {
            $reflection = new \ReflectionMethod($class,$method);
            $comment = $reflection->getDocComment();
            
            if (!empty($comment)) 
            {
                $metadata = self::_parse($comment);
            } 
            else 
            {
                $metadata = null;
            }
            
            return $metadata;
        }

        /**
         * @name getPropertyMetaData
         * @description retrieves property metadata
         * @access static
         * @param string $class 
         * @param string $property 
         * @return array
         */
        public static function getPropertyMetaData($class,$property) 
        {
            $reflection = new \ReflectionProperty($class,$property);
            $comment = $reflection->getDocComment();
            
            if (!empty($comment)) 
            {
                $metadata = self::_parse($comment);
            } 
            else 
            {
                $metadata = null;
            }
            
            return $metadata;
        }

        /**
         * @name getClassProperties
         * @description retrieves class properties
         * @access static
         * @param string $class 
         * @return array
         */
        public static function getClassProperties($class) 
        {
            $properties = array();
            $reflection = new \ReflectionClass($class);
            $reflectionProperties = $reflection->getProperties();
            foreach ($reflectionProperties as $property) 
            {
                $properties[] = $property->getName();
            }
            return $properties;
        }

        /**
         * @name getClassMethods
         * @description retrieves class methods
         * @access static
         * @param string $class  
         * @return array
         */
        public static function getClassMethods($class) 
        {
            $methods = array();
            $reflection = new \ReflectionClass($class);
            $reflectionMethods = $reflection->getMethods();
            foreach ($reflectionMethods as $method) 
            {
                $methods[] = $method->getName();
            }
            return $methods;
        }

        /**
         * @name _parse
         * @description parses a prologue of classes / methods / properties ....
         * @access protected | static
         * @param string $comment 
         * @return array
         */
        protected static function _parse($comment) 
        {
            $meta = array();
            $pattern = "(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_]*)";
            $matches = Strings::match($comment, $pattern);
            if ($matches != null) 
            {
                foreach ($matches as $match) 
                {
                    $parts = Arrays::clean(Arrays::trim(Strings::split($match, "[\s]", 2)));
                    $meta[$parts[0]] = true;
                    
                    if (sizeof($parts) > 1) 
                    {
                        $meta[$parts[0]] = Arrays::clean(Arrays::trim(Strings::split($parts[1], ",")));
                    }
                }
            }
            return $meta;
        }
    }
}