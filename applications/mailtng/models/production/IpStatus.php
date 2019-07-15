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
     * @name            IpStatus.model 
     * @description     The IpStatus model
     * @package		ma\applications\mailtng\models\production
     * @category        Model
     * @author		MailTng Team			
     */
    class IpStatus extends Model
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
        protected $_table = 'ip_status';

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
        protected $_ip_id;

        /**
	 * @column
	 * @readwrite
	 * @type timestamp
         * @nullable false
	 * @length
	 */
        protected $_status_date;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 255
	 */
        protected $_x_store_info;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 255
	 */
        protected $_x_message_delivery;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 255
	 */
        protected $_x_message_info;
    }
}