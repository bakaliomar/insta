<?php namespace ma\applications\mailtng\models\data
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
     * @name            Fresh.model 
     * @description     The Fresh model
     * @package		ma\applications\mailtng\models\data
     * @category        Model
     * @author		MailTng Team			
     */
    class Fresh extends Model
    {
        #table 
        
        /**
         * @schema
         * @readwrite
         */
        protected $_schema = '';
        
        /**
         * @table
         * @readwrite
         */
        protected $_table = 'fresh_';

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
	 * @type text
         * @nullable false
	 * @length 500
	 */
        protected $_email;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_offers_excluded;
    }
}