<?php namespace ma\mailtng\os
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
     * @name            System.class 
     * @description     It's a class that deals with os methods like getting load average , executing a command ....
     * @package		ma\mailtng\os
     * @category        Helper Class
     * @author		MailTng Team			
     */
    class System extends Base
    {
        /**
         * @name executeCommand
         * @description executes a system command
         * @access static
         * @param string $command
         * @param true $outputInArray
         * @return array
         */
        public static function executeCommand($command,$outputInArray = false) 
        {
            $result = array("output" => "" , "error" => "");
            if(isset($command) && $command != '')
            {
                $descriptorspec = array(
                        0 => array("pipe", "r"), 
                        1 => array("pipe", "w"),
                        2 => array("pipe", "w"),
                );
                $pipes = array();
                $process = proc_open($command, $descriptorspec,$pipes, dirname(__FILE__), null);  

                if(is_resource($process))
                {
                    $result["output"] = $outputInArray ? explode("\n",trim(stream_get_contents($pipes[1]))) : stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    $result["error"] = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);

                    // close the proccess
                    proc_close($process);
                }
            }
            return $result;
        }

        /**
         * @name isServiceRunning
         * @description executes a system command
         * @access static
         * @param string $serviceName
         * @return boolean
         */
        public static function isServiceRunning($serviceName) 
        {
            if(isset($serviceName) && $serviceName != '')
            {
                $output = `ps aux | grep /usr/sbin/$serviceName | grep -v 'grep' | awk '{ print $8 }'`;    
                if(strtolower(trim(str_replace(PHP_EOL,'',$output))) === 'ssl')
                {
                    return true;
                }
            }
            return false;
        }

        /**
         * @name getLoadAverage
         * @description gets the server's load average
         * @access static
         * @return integer
         */
        public static function getLoadAverage() 
        {
            $load = sys_getloadavg();
            return $load[0];
        }
    }
}

