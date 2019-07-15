<?php namespace ma\mailtng\templating
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
    use ma\mailtng\exceptions\types\TemplateException as TemplateException;
    /**
     * @name            Implementation.class 
     * @description     The main implementation class that all upcoming template implementations have to extends from it. 
     * @package		ma\mailtng\templating
     * @category        Templating Class
     * @author		MailTng Team			
     */
    class Implementation extends Base
    {
        /**
         * @name handle
         * @description creates a working function for the template 
         * @access public
         * @param array $node the mapping array node
         * @param string $content the content to parse
         * @return
         * @throws TemplateException
         */
        public function handle($node, $content) 
        {
            try 
            {
                $handler = $this->_handler($node);
                return call_user_func_array(array($this, $handler), array($node, $content));
            } 
            catch (\Exception $exception) 
            {
                throw new TemplateException($exception->getMessage(),500,$exception);
            }
        }

        /**
         * @name match
         * @description evaluates a $source string to determine if it matches a tag or statement.
         * @access public
         * @param string $source the template content 
         * @return array
         * @throws TemplateException
         */
        public function match($source) 
        {
            $type = null;
            $delimiter = null;
            foreach ($this->_map as $_delimiter => $_type) 
            {
                if (!$delimiter || Strings::indexOf($source, $type["opener"]) == -1) 
                {
                    $delimiter = $_delimiter;
                    $type = $_type;
                }
                
                $indexOf = Strings::indexOf($source, $_type["opener"]);
                
                if ($indexOf > -1) 
                {
                    if (Strings::indexOf($source, $type["opener"]) > $indexOf) 
                    {
                        $delimiter = $_delimiter;
                        $type = $_type;
                    }
                }
            }
            
            if ($type == null) 
            {
                return null;
            }
            
            return array(
                "type" => $type,
                "delimiter" => $delimiter
            );
        }
        
        /**
         * @name _handler
         * @description determines the correct handler method to execute
         * @access protected
         * @param array $node The mapping array node
         * @return string
         */
        protected function _handler($node) 
        {
            if (empty($node["delimiter"])) 
            {
                return null;
            }

            if (!empty($node["tag"])) 
            {
                return $this->_map[$node["delimiter"]]["tags"][$node["tag"]]["handler"];
            }

            return $this->_map[$node["delimiter"]]["handler"];
        }
    }
}