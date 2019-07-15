<?php namespace ma\mailtng\encryption
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
     * @name            Crypto.class 
     * @description     It's a class that deals with encrypting , hashing and coding methods
     * @package		ma\mailtng\encryption
     * @category        Helper Class
     * @author		MailTng Team			
     */
    class Crypto extends Base
    {
        /** 
         * @readwrite
         * @access static 
         * @var string
         */ 
        private static $secretKey = "vY?{Uq~Xxz5t%\&,,zDc>_Z}pWLDh.A7";
         
        /**
         * @name AESEncrypt
         * @description encrypt a value using AES 256 Algorithem
         * @access static
         * @param string $value
         * @param string
         * @return
         */
        public static function AESEncrypt($value)
        {
            if(!empty($value))
            {
                if(!is_string($value) && !is_numeric($value))
                {
                    $encryptedValue = serialize($value);
                }
                else
                {
                    $encryptedValue = $value;
                }
                
                return rtrim(
                base64_encode(
                    mcrypt_encrypt(
                        MCRYPT_RIJNDAEL_256,
                        Crypto::$secretKey, $encryptedValue, 
                        MCRYPT_MODE_ECB, 
                        mcrypt_create_iv(
                            mcrypt_get_iv_size(
                                MCRYPT_RIJNDAEL_256, 
                                MCRYPT_MODE_ECB
                            ), 
                            MCRYPT_RAND)
                        )
                    ), "\0"
                );
            }
            
            return $value;
        }

        /**
         * @name AESEncrypt
         * @description decrypt an AES 256 Algorithem encrypted value
         * @access static
         * @param string $value
         * @param string
         * @return
         */
        public static function AESDecrypt($value)
        {
            if(!empty($value))
            {
                $value = rtrim(
                        mcrypt_decrypt(
                        MCRYPT_RIJNDAEL_256, 
                        self::$secretKey, 
                        base64_decode($value), 
                        MCRYPT_MODE_ECB,
                        mcrypt_create_iv(
                            mcrypt_get_iv_size(
                                MCRYPT_RIJNDAEL_256,
                                MCRYPT_MODE_ECB
                            ), 
                            MCRYPT_RAND
                        )
                    ), "\0"
                );

                if(@unserialize($value))
                {
                    $value = @unserialize($value);
                }
            }
            
            return $value;
        }       
    }
}