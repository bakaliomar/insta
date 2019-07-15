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
     * @name            Sponsor.model 
     * @description     The Sponsor model
     * @package		ma\applications\mailtng\models\admin
     * @category        Model
     * @author		MailTng Team			
     */
    class Sponsor extends Model
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
        protected $_table = 'sponsors';

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
        protected $_status_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_affiliate_id;
        
	/**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 20
	 */
        protected $_name;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 500
	 */
        protected $_website;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 100
	 */
        protected $_username;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 100
	 */
        protected $_password;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_api_key;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_api_url;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_api_type;
         
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_created_by;
         
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable true
	 * @length
	 */
        protected $_last_updated_by;
        
        /**
	 * @column
	 * @readwrite
	 * @type date
         * @nullable false
	 * @length
	 */
        protected $_created_at;
         
        /**
	 * @column
	 * @readwrite
	 * @type date
         * @nullable true
	 * @length
	 */
        protected $_last_updated_at;
    }
}