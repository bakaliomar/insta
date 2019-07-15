<?php namespace ma\mailtng\compressing
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
     * @name            Zip.class 
     * @description     It's a class that deals with zipping / unzipping mechanism
     * @package		ma\mailtng\compressing
     * @category        Helper Class
     * @author		MailTng Team			
     */
    class Zip extends Base
    {
        /**
         * @name extractZipFileTo
         * @description extracts a zip file to a given directory
         * @access static
         * @param  string $fileName
         * @return int
         */
        public static function extractZipFileTo($fileName,$extractPath)
        {
            if (file_exists($extractPath))
            {
                $zip = new \ZipArchive();
                $res = $zip->open($fileName);

                if ($res === true)
                {
                    $zip->extractTo($extractPath);
                    $zip->close();
                    return true;
                }
            }

            return false;
        }

        /**
         * @name getFileNames
         * @description gets the names of all the files in a specefic zip file
         * @access static
         * @param string $zipFile
         * @param array
         * @return
         */
        public static function getFileNames($zipFile)
        {
            $names = [];
            $zip = new \ZipArchive();
            $res = $zip->open($zipFile);

            if ($res === true)
            {
                for ($i = 0; $i < $zip->numFiles; $i++)
                {
                    $names[] = $zip->getNameIndex($i);
                }
            }

            return $names;
        }
    }
}