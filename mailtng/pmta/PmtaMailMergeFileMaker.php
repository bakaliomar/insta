<?php namespace ma\mailtng\pmta
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
    use ma\mailtng\registry\Packager as Packager;
    use ma\mailtng\types\Objects as Objects;
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\exceptions\types\PMTAException as PMTAException;
    /**
     * @name            PmtaMailMergeFileMaker.class 
     * @description     The PmtaMailMergeFileMaker class
     * @package		ma\mailtng\pmta
     * @category        Helper
     * @author		MailTng Team			
     */
    class PmtaMailMergeFileMaker extends Base
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_pickupFilePrefix = "pk_message_";
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_xmrgFrom;
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_message;
        
        /** 
         * @readwrite
         * @access protected 
         * @var PmtaHeader
         */ 
        protected $_header;
        
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_pmtaMailMergeSections = array();
        
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_smtpConfiguration = array(
            "host" => "",
            "login" => "",
            "password" => "",
            "port" => 25
        );

        /**
         * @name addPmtaMailMergeSection
         * @description add PMTA MailMerge section
         * @access public
         * @param mixed $pmtaMailMergeSection
         * @return string
         */
        public function addPmtaMailMergeSection($pmtaMailMergeSection) 
        {
            if(isset($pmtaMailMergeSection) && is_object($pmtaMailMergeSection))
            {
                $this->_pmtaMailMergeSections[] = $pmtaMailMergeSection;
            }
        }

        /**
         * @name createPickupFile
         * @description create a mailmerge file for pickup directory
         * @access public
         * @param string $prefix
         * @return boolean true if the component has been created and false if something went wrong
         */
        public function createPickupFile($prefix = null,$contentOnly = false,$header = '',$jobId = 0) 
        {
            # get applications configuration to get pmta config
            $currentApplication = Packager::get('application');

            if(isset($currentApplication))
            {
                $initConfigurations = Objects::objectToArray($currentApplication->getSetting("init"));

                if(isset($initConfigurations))
                {
                    # check if prefix is null if yes set the default one to it 
                    $prefix = (isset($prefix) && strlen($prefix)) ? $prefix : $this->getPickupFilePrefix();

                    #add a random text to the file name to prevents duplicate names
                    $fileName = $prefix . Strings::generateRandomText(8,true,true,true,false) . "_" . date_timestamp_get(date_create());
                    
                    if($jobId > 0)
                    {
                        $header .= "x-job: mailer_$jobId" . PHP_EOL;
                    }
                    
                    # mailmerge part 
                    $mailMerge = $this->generateMailMerge();
                    
                    # put all the peaces together
                    $fileContent = "XACK ON " . PHP_EOL;
                    $fileContent .= "XMRG FROM: " . $this->getXmrgFrom() . PHP_EOL;
                    $fileContent .= $mailMerge . 'XPRT 1 LAST ' . PHP_EOL . $header . $this->getMessage() . PHP_EOL . '.' . PHP_EOL;
                    
                    if($contentOnly == true)
                    {
                        return $fileContent;
                    }
                    else
                    {
                        # start creating the file 
                        $pickupDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'sync' . DS;

                        if(is_dir($pickupDirectory))
                        {
                            $filePath = $pickupDirectory . $fileName;

                            # create the pickup file
                            if(file_put_contents($filePath,$fileContent))
                            {
                                return $fileName;
                            }
                        }
                        else
                        {
                            throw new PMTAException("Pickup Directory : $pickupDirectory Not Found !", 500);
                        }
                    }
                }
            }

            return false;
        }

        /**
         * @name generateMailMerge
         * @description generate PMTA MailMerge section
         * @access public
         * @return mixed
         */
        public function generateMailMerge()
        {
            $mailMerge = '';
            
            if($this->_pmtaMailMergeSections != null && count($this->_pmtaMailMergeSections))
            {
                foreach ($this->_pmtaMailMergeSections as $section) 
                {
                    if(isset($section) && is_object($section))
                    {
                        $vmta = 'XDFN *vmta=' . $section->getVmta() . PHP_EOL;
                        $from = 'XDFN *from=' . $section->getFrom() . PHP_EOL;
                        $vmta = 'XDFN *vmta=' . $section->getVmta() . PHP_EOL;
                        $rcptTo = 'RCPT TO:<' . $section->getRcptTo() . '>' . PHP_EOL;
                       
                        # xdfns part
                        $xdfns = 'XDFN  ';

                        if($section->getXdfns() != null && count($section->getXdfns()))
                        {
                            foreach ($section->getXdfns() as $key => $value) 
                            {
                                if(isset($value) && strlen($value) > 0)
                                {
                                    $xdfns .= $key . '="' . $value . '"' . " ";
                                }
                            }
                        }
                        
                        $xdfns .= PHP_EOL;
                        
                        # add the parts to the mailmerge section 
                        $mailMerge .= $from . $vmta . $xdfns . $rcptTo; 
                    } 
                }
            }
            
            return $mailMerge;
        }  
    }
}
