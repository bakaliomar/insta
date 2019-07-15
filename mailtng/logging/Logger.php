<?php namespace ma\mailtng\logging
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
    use ma\mailtng\files\Files as Files;
    use ma\mailtng\exceptions\types\LoggingException as LoggingException;
    /**
     * @name            Logger.class 
     * @description     It's a class that deals with logging mechanism 
     * @package		ma\mailtng\logging
     * @category        Logging Class
     * @author		MailTng Team			
     */
    class Logger
    {     
        /**
         * @name __construct
         * @description private constructor to prevent it being created directly
         * @access private
         * @return
         */
        private function __construct()  
        {}  

        /**
         * @name writeLogMessage
         * @description write the log message in its appropriate file
         * @access private | static
         * @param string $message 
         * @param string $filePath 
         * @param string $logType 
         * @return string
         * @throws LoggingException
         */
        private static function writeLogMessage($message,$filePath,$logType) 
        {
            try 
            {     
                $time = @date('[d/m/Y:H:i:s]');
                
                # Check if the log file requires recycling
                self::checkLogFileRecycling($filePath);
                
                # change ownership 
                exec("chown apache:apache $filePath");
                
                file_put_contents($filePath,\utf8_encode($time .' ['.$logType.'] : '.$message ) . PHP_EOL,FILE_APPEND | LOCK_EX);
                
                # change ownership 
                exec("chown apache:apache $filePath");
            }
            catch (\Exception $exception) 
            {
                throw new LoggingException($exception->getMessage(),$exception->getCode(),$exception);
            }    
        }

        /**
         * @name checkLogFileRecycling
         * @description checks if a specific log file has reached the max lenght defined by config.ini.php if yes it archives the current one and creates a new one instead
         * @access private | static
         * @param string $filePath  
         * @return
         * @throws LoggingException
         */
        private static function checkLogFileRecycling($filePath)
        {
            try 
            {
                $fileSize = intval(Files::getFileSizeInMegaBytes(filesize($filePath)));
                if($fileSize >=  intval(MAX_RECYCLING_SIZE))
                {
                    $oldFilePath = dirname($filePath) . DS;
                    $newFilePath = dirname($filePath) . DS . 'archive' . DS;
                    rename($oldFilePath.basename($filePath),$oldFilePath.'log_'.date('Y_m_d_H_i').'_'.basename($filePath));
                    copy($oldFilePath.'log_'.date('Y_m_d_H_i').'_'.basename($filePath),$newFilePath.'log_'.date('Y_m_d_H_i').'_'.basename($filePath));
                    unlink($oldFilePath.'log_'.date('Y_m_d_H_i').'_'.basename($filePath));
                }
            } 
            catch (\Exception $exception) 
            { 
                 throw new LoggingException($exception->getMessage(),$exception->getCode(),$exception);
            }
        }

        /**
         * @name error
         * @description log an error message
         * @access private | static
         * @param mixed $error  
         * @return
         * @throws LoggingException
         */
        public static function error()
        {
            $arg = func_get_args();
            if(isset($arg))
            {
                if(is_array($arg))
                {
                    if(count($arg) == 1)
                    {
                        $message = "";
                        if(is_object($arg[0]))
                        {
                            $exc = $arg[0];
                            $line = $exc->getLine();
                            $file = $exc->getFile();
                            $message = str_replace('\\n','', $exc->getMessage()).' in : '.$file.' at line : '.$line;                    
                            $message = trim(preg_replace('/\s+/', ' ', $message));  
                        }
                        elseif(is_string($arg[0]))
                        {
                            $message = $arg[0];                  
                        }       
                                 
                        self::writeLogMessage($message,ERROR_FILE,'error');
                    }
                }
            }
        }

        /**
         * @name debug
         * @description log an debug message
         * @access private | static
         * @param mixed $error  
         * @return
         * @throws LoggingException
         */
        public static function debug()
        {
            $arg = func_get_args();
            if(isset($arg))
            {
                if(is_array($arg))
                {
                    if(count($arg) == 1)
                    {
                        $message = "";
                        if(is_object($arg[0]))
                        {
                            $exc = $arg[0];
                            $line = $exc->getLine();
                            $file = $exc->getFile();
                            $message = str_replace('\\n','', $exc->getMessage()).' in : '.basename($file).' at line : '.$line;                    
                            $message = trim(preg_replace('/\s+/', ' ', $message));                      
                        }
                        elseif(is_string($arg[0]))
                        {
                            $message = $arg[0];                  
                        }              

                        self::writeLogMessage($message,DEBUG_FILE,'debug');
                    }
                }
            }
        }
    }
}