<?php namespace ma\mailtng\pmta
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
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\types\Strings as Strings;
    /**
     * @name            PmtaHeader.class 
     * @description     The PmtaHeader class
     * @package		ma\mailtng\pmta
     * @category        Helper
     * @author		MailTng Team			
     */
    class PmtaHeader extends Base
    {
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_parameters = array();

        /**
         * @name convertHeaderTextToParameters
         * @description converts a header string to array of parameters
         * @access public
         * @param string $header
         * @return
         */
        public function convertHeaderTextToParameters($header) 
        {
            if(isset($header) && is_string($header))
            {
                $parameters = explode(PHP_EOL,$header);
                
                foreach ($parameters as $row) 
                {
                    if(Strings::indexOf($row,":"))
                    {
                        $rowParts = explode(":",$row);
                        
                        if(count($rowParts) == 2)
                        {
                            $this->_parameters[trim($rowParts[0])] = trim($rowParts[1]);
                        }
                        elseif(count($rowParts) > 2)
                        {
                            $tempArray = array();
                            
                            for ($index = 1; $index < count($rowParts); $index++) 
                            {
                                $tempArray[] = trim($rowParts[$index]);
                            }
                            
                            $this->_parameters[trim($rowParts[0])] = trim(implode(":",$tempArray));
                        }
                    }
                }
            }
        }

        /**
         * @name addHeaderParameters
         * @description merge new header parameters into the existant parametes
         * @access public
         * @param array $parameters
         * @return
         */
        public function addHeaderParameters($parameters) 
        {
            if(isset($parameters) && is_array($parameters))
            {
                $this->_parameters = array_merge($this->_parameters, $parameters);
            }
        }

        /**
         * @name addHeaderParameter
         * @description add a new header parameter
         * @access public
         * @param string $key
         * @param string $value
         * @return
         */
        public function addHeaderParameter($key,$value) 
        {
            if(isset($key) && isset($value))
            {
                $this->_parameters[$key] = $value;
            }
        }

        /**
         * @name getHeaderParameter
         * @description finds and retrieves a header value by its key
         * @access public
         * @param string $key
         * @return mixed
         */
        public function getHeaderParameter($key) 
        {
            if(isset($key) && array_key_exists($key,$this->_parameters))
            {
                return $this->_parameters[$key];
            }
            
            return NULL;
        }

        /**
         * @name deleteHeaderParameter
         * @description finds and deletes a header value by its key
         * @access public
         * @param string $header
         * @return
         */
        public function deleteHeaderParameter($key) 
        {
            if(isset($key) && array_key_exists($key,$this->_parameters))
            {
                Arrays::removeElement($this->_parameters, $key);
            }
        }

        /**
         * @name generateHeader
         * @description generates the header
         * @access public
         * @return string
         */
        public function generateHeader() 
        {
            # the text value of the header 
            $header = '';
            
            if(isset($this->_parameters) && count($this->_parameters))
            {
                foreach ($this->_parameters as $key => $value) 
                {
                    if(substr($key,0,2) == '__')
                    {
                        $header .= $value . PHP_EOL;
                    }
                    else
                    {
                        $header .= $key . ": " . $value . PHP_EOL;
                    }
                }
            }
            
            return $header;
        }
    }
}