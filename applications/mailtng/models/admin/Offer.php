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
     * @name            Offer.model 
     * @description     The Offer model
     * @package		ma\applications\mailtng\models\admin
     * @category        Model
     * @author		MailTng Team			
     */
    class Offer extends Model
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
        protected $_table = 'offers';

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
        protected $_sponsor_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_production_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_campaign_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_vertical_id;

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
        protected $_flag;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_description;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 20
	 */
        protected $_rate;
        
        /**
	 * @column
	 * @readwrite
	 * @type date
         * @nullable false
	 * @length
	 */
        protected $_launch_date;
        
        /**
	 * @column
	 * @readwrite
	 * @type date
         * @nullable false
	 * @length
	 */
        protected $_expiring_date;
 
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_rules;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 20
	 */
        protected $_epc;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_suppression_list; 
         
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
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 10
	 */
        protected $_key;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_authorized_users;
    }
}