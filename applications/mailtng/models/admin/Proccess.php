<?php namespace ma\applications\mailtng\models\admin
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
    use ma\mailtng\application\Model as Model;
    /**
     * @name            Proccess.model 
     * @description     The Proccess model
     * @package		ma\applications\mailtng\models\admin
     * @category        Model
     * @author		MailTng Team			
     */
    class Proccess extends Model
    {
        #table 
        
        /**
         * @schema
         * @readwrite
         */
        protected $_schema = 'admin';
        
        /**
         * @table
         * @readwrite
         */
        protected $_table = 'proccesses';

        # Columns 

        /**
	 * @column
	 * @readwrite
         * @autoincrement
         * @primary
	 * @type integer
         * @nullable false
	 * @length 
	 */
        protected $_id;

        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length 
	 */
        protected $_user_id;
        
	/**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length
	 */
        protected $_name;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length
	 */
        protected $_type;

        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 20
	 */
        protected $_status;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length
	 */
        protected $_progress;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_data;
        
        /**
	 * @column
	 * @readwrite
	 * @type timestamp
         * @nullable false
	 * @length
	 */
        protected $_start_time;
        
        /**
	 * @column
	 * @readwrite
	 * @type timestamp
         * @nullable true
	 * @length
	 */
        protected $_finish_time;
    }
}