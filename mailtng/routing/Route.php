<?php namespace ma\mailtng\routing
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
     * @name            Route.class 
     * @description     It represents the different kinds of routes that we can define in our framework's configuration
     * @package		ma\mailtng\routing
     * @category        Routing Class
     * @author		MailTng Team			
     */
    class Route extends Base 
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_pattern;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_controller;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_action;

        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_parameters = array();
    }
}