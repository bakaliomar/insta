<?php namespace ma\mailtng\api
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
    use ma\mailtng\configuration\Configuration as Configuration;
    use ma\mailtng\files\Paths as Paths;
    use ma\mailtng\exceptions\types\BackendException as BackendException;
    /**
     * @name            NameCheap.class 
     * @description     It's a class that deals with NameCheap API methods
     * @package		ma\mailtng\api
     * @category        API
     * @author		MailTng Team			
     */
    class NameCheap extends Base
    {

        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_url;

        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_username;
        
        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_apiKey;

        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_ip;
    
        /*
         * instantiate a namecheap object
         * @credentials array associative array of namecheap API credentials
         * @sandbox boolean whether to use the Namecheap Sandbox or the real site
         * @return object a namecheap object
         */
        public function __construct($sandbox = false)
        {
            parent::__construct([]);
            
            if ($sandbox)
            {
                $this->_url = 'https://api.sandbox.namecheap.com/xml.response';
            }
            else
            {
                $this->_url = 'https://api.namecheap.com/xml.response';
            }

            $configuration = new Configuration(array( "type" => "ini" ));
            $result = $configuration->initialize()->parse(Paths::getCurrentApplicationRealPath() . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'namecheap',false);
  
            if(count($result))
            {
                $this->_username = $result['username'];
                $this->_apiKey = $result['key'];
                $this->_ip = $result['ip'];        
            }
        }
        
        /**
         * @name getResponse
         * @description parses the xml returned by a response 
         * @param string $command
         * @param array $parameters
         */
        public function getResponse($command,$parameters)
        {
            $response = null;
            
            if(strlen($command) > 0)
            {
                $url = $this->_url . '?ApiUser=' . $this->_username . '&ApiKey=' . $this->_apiKey . '&UserName=' . $this->_username . '&ClientIP=' . $this->_ip . '&Command=' . $command;

                foreach ($parameters as $arg => $value)
                {
                    $url .= "&$arg=";
                    $url .= urlencode($value);
                }

                $content = file_get_contents($url);
                
                if(strlen($content) > 0)
                {
                    $xml = new \SimpleXMLElement($content);

                    if(isset($xml))
                    {
                        if($xml['Status'] == 'ERROR')
                        {
                            throw new BackendException($xml->Errors->Error);
                        }
                        elseif ($xml['Status'] == 'OK')
                        {
                            $response = $xml->CommandResponse;
                        }
                    }
                }
            }
            
            return $response;
        }

        /**
         * @name getAllDomains
         * @description get all domains
         * @param string $type
         * @param integer $page
         * @param integer $pagesize
         * @param string $sort
         * @param string $search
         * @return array $domains
         */
        public function getAllDomains($type = 'all', $page = 1, $pagesize = 100, $sort = 'NAME', $search = '')
        {
            $domains = array();
            
            # get response
            $response = $this->getResponse('namecheap.domains.getList',array('ListType' => $type, 'SearchTerm' => $search, 'Page' => $page, 'PageSize' => $pagesize, 'SortBy' => $sort));
            
            if($response != null)
            {
                foreach ($response->DomainGetListResult->Domain as $domain)
                {
                    $temp = array();
                    
                    foreach ($domain->attributes() as $key => $value)
                    {
                        $temp[$key] = (string) $value;
                    }
                    
                    $domains[] = $temp;
                }
            }

            return $domains;
        }
        
        /**
         * @name setDomainRecords
         * @description set DNS Records to a domain
         * @param string $domain
         * @param array $records
         * @return boolean
         */
        public function setDomainRecords($domain,$records)
        {
            list( $records['SLD'], $records['TLD'] ) = explode('.', $domain);
            
            $response = $this->getResponse('namecheap.domains.dns.setHosts',$records);
            
            if($response != null)
            {
                if(strtolower($response->DomainDNSSetHostsResult->attributes()->IsSuccess) == 'true')
                {
                    return true;
                }
            }

            return false;
        }
    }
}



