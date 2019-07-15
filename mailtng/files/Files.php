<?php namespace ma\mailtng\files
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
    use ma\mailtng\http\Request as Request;
    use ma\mailtng\os\System as System;
    use ma\mailtng\exceptions\types\UploadException as UploadException;
    /**
     * @name            Files.class 
     * @description     It's a class that deals with files methods
     * @package		ma\mailtng\files
     * @category        Helper Class
     * @author		MailTng Team			
     */
    class Files extends Base
    {
        /**
         * @name getFileSizeInMegaBytes
         * @description converts a file size into Megabytes
         * @access static
         * @param  int $sizenBIytes
         * @return int
         */
        public static function getFileSizeInMegaBytes($sizenBIytes)
        {
            if(is_numeric($sizenBIytes))
            {
                return number_format($sizenBIytes / 1048576, 2);
            }
            return 0 ;
        }

        /**
         * @name getFilesFromDirs
         * @description get files from directories
         * @access static
         * @return 
         */
        public static function getFilesFromDirs($directory)
        {
            $directory = rtrim($directory,DS);
            $result = System::executeCommand("ls -l $directory/*/* | cut -f2 | awk '{ print $9}'",true);
            return $result['output'];
        }
        
        /**
         * @name getFileSizeInMegaBytes
         * @description uploads a file into a given folder
         * @access static
         * @param string $uploadsPath The folder path
         * @param string $name The file name
         * @param string $file The file itself
         * @param integer $size The size of the file
         * @param string $type The type of the file
         * @param string $ext The extension of the file
         * @param  integer $max_size The max size 
         * @return boolean
         */
        public static function uploadFile($uploadsPath, $name, $file, $size, $type, $ext, $max_size = 1.5) 
        {
            if (isset($uploadsPath)) 
            {
                if (is_dir($uploadsPath)) 
                {
                    if (intval($max_size) > self::getFileSizeInMegaBytes(intval($size))) 
                    {
                        if (in_array($type, Request::$mimes[$ext])) 
                        {
                            $nameParts = explode(DS, $name);
                            $name = $nameParts[count(explode(DS, $name)) - 1];
                            try 
                            {
                                $imageUploadStatus = move_uploaded_file($file, $uploadsPath . $name);
                            } 
                            catch (Exception $exception) 
                            {
                                throw new UploadException($exception->getMessage(),$exception->getCode(),$exception);
                            }
                        } 
                        else 
                        {
                            throw new UploadException('Unsupported file format');
                        }
                    } 
                    else 
                    {
                        throw new UploadException('File size has exceeded the limits');
                    }
                }
            }
            return $imageUploadStatus;
        }
    }
}