<?php namespace ma\applications\mailtng\models\statistics
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
     * @name            Unsub.model 
     * @description     The Unsub model
     * @package		ma\applications\mailtng\models\statistics
     * @category        Model
     * @author		MailTng Team			
     */
    class Unsub extends Model
    {
        #table 
        
        /**
         * @schema
         * @readwrite
         */
        protected $_schema = 'stats';
        
        /**
         * @table
         * @readwrite
         */
        protected $_table = 'unsubs';

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
        protected $_drop_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 100
	 */
        protected $_email;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 20
	 */
        protected $_type;
        
        /**
	 * @column
	 * @readwrite
	 * @type timestamp
         * @nullable false
	 * @length
	 */
        protected $_action_date;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 100
	 */
        protected $_list;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_message;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 20
	 */
        protected $_ip;

        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_country;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_region;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_city;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 2
	 */
        protected $_language;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 
	 */
        protected $_device_type;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 100
	 */
        protected $_device_name;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 
	 */
        protected $_os;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 
	 */
        protected $_browser_name;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 100
	 */
        protected $_browser_version;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable true
	 * @length
	 */
        protected $_action_occurences;
    }
}