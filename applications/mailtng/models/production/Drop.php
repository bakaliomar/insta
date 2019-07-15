<?php namespace ma\applications\mailtng\models\production
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
     * @name            Drop.model 
     * @description     The Drop model
     * @package		ma\applications\mailtng\models\production
     * @category        Model
     * @author		MailTng Team			
     */
    class Drop extends Model
    {
        #table 
        
        /**
         * @schema
         * @readwrite
         */
        protected $_schema = 'production';
        
        /**
         * @table
         * @readwrite
         */
        protected $_table = 'drops';

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
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_server_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable true
	 * @length
	 */
        protected $_isp_id;
        
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
         
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_total_emails;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable true
	 * @length
	 */
        protected $_sent_progress;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_offer_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_offer_from_name_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_offer_subject_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_recipients_emails;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_pids;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_header;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_creative_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_lists;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length
	 */
        protected $_post_data;
    }
}