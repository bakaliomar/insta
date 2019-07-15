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
    /**
     * @name            Rotator.class 
     * @description     The Rotator class
     * @package		ma\mailtng\pmta
     * @category        Helper
     * @author		MailTng Team			
     */
    class Rotator extends Base
    {
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_list = array();
        
        /** 
         * @readwrite
         * @access protected 
         * @var integer
         */ 
        protected $_listCount = 0;
        
        /** 
         * @readwrite
         * @access protected 
         * @var integer
         */ 
        protected $_index = 0;
        
        /** 
         * @readwrite
         * @access protected 
         * @var integer
         */ 
        protected $_rotateAfter = 0;
        
        /** 
         * @readwrite
         * @access protected 
         * @var integer
         */ 
        protected $_counter = 1;

        /**
         * @name __construct
         * @description contructor of the rotator
         * @access public
         * @param array $list
         * @param integer $rotation
         * @return Rotator
         */
        public function __construct($list,$rotation = 1) 
        {
            $this->_rotateAfter = intval($rotation) > 0 ? intval($rotation) : 1;
            $this->_list = $list;
            $this->_listCount = count($list);
            $this->_index = 0;
            $this->_counter = 1;
        }

        /**
         * @name rotateAfter
         * @description rotate after a given number
         * @access public
         * @param integer $rotation
         * @return Rotator
         */
        public function rotateAfter($rotation) 
        {
            if(isset($rotation) && is_numeric($rotation) && intval($rotation) >= 1)
            {
                $this->_rotateAfter = $rotation;
            }
            
            return $this;
        }

        /**
         * @name reset
         * @description reset everything in the rotator
         * @access public
         * @param integer $rotation
         * @return Rotator
         */
        public function reset() 
        {
            $this->_index = 0;
            $this->_counter = 1;
        }
        
        /**
         * @name rotate
         * @description rotate by the rotate after value
         * @access public
         * @return
         */
        public function rotate() 
        {
            if($this->_counter == $this->_rotateAfter)
            {
                $this->_index++;
                
                if($this->_index == $this->_listCount)
                {
                    $this->_index = 0;
                }

                $this->_counter = 0;
            }
            
            $this->_counter++;
        }

        /**
         * @name getCurrentValue
         * @description get the current value
         * @access public
         * @return mixed
         */
        public function getCurrentValue() 
        {
            if($this->_listCount > 0)
            {
                return $this->_list[$this->_index];
            }
            
            return '';
        }
        
        /**
         * @name setCurrentValue
         * @description set the current value
         * @access public
         * @param string $value
         * @return
         */
        public function setCurrentValue($value) 
        {
            if(isset($value))
            {
                $this->_list[$this->_index] = $value;
            }
        }
        
        /**
         * @name replaceInCurrent
         * @description replace a value in the current index
         * @access public
         * @param string $searchValue
         * @param string $replaceValue
         * @return
         */
        public function replaceInCurrent($searchValue,$replaceValue) 
        {
            if(isset($searchValue) && isset($replaceValue) && $this->_listCount > 0)
            {
                $this->_list[$this->_index] = str_replace($searchValue,$replaceValue,$this->_list[$this->_index]);
            }
        }
    }
}