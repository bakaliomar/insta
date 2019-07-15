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
     * @name            PmtaMailMergeSection.class 
     * @description     The PmtaMailMergeSection class
     * @package		ma\mailtng\pmta
     * @category        Helper
     * @author		MailTng Team			
     */
    class PmtaMailMergeSection extends Base
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_rcptTo;
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_vmta;
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_from;
        
        /** 
         * @read
         * @access protected 
         * @var array
         */ 
        protected $_xdfns;

        /**
         * @name addXDFN
         * @description add XDFN to the existant XDFN parameters
         * @access public
         * @param string $key
         * @param string $value
         * @return
         */
        public function addXDFN($key,$value) 
        {
            if(isset($key) && isset($value))
            {
                $this->_xdfns[$key] = $value;
            }
        }
    }
}