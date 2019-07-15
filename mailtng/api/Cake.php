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
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\www\URL as URL;
    use ma\mailtng\os\System as System;
    use ma\mailtng\logging\Logger as Logger;
    /**
     * @name            Cake.class 
     * @description     It's a class that deals with Cake API methods
     * @package		ma\mailtng\api
     * @category        API
     * @author		MailTng Team			
     */
    class Cake extends Base
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
        protected $_email;

        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_password;
        
        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_key;

        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_affiliateId;
    
        /**
         * @name getResponse
         * @description parses the xml returned by a response 
         * @param string $url
         * @param array $parameters
         */
        public function getResponse($url,$parameters,$raw = false)
        {
            $result = [];
            
            if(filter_var($url,FILTER_VALIDATE_URL))
            {
               # Initiate the REST call via curl
                $ch = \curl_init($url . '?' . http_build_query($parameters));
                \curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0");
                \curl_setopt($ch, CURLOPT_FAILONERROR, true);
                \curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                \curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                \curl_setopt($ch, CURLOPT_VERBOSE, false);
                
                # Set the HTTP method to GET
                \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                
                # Don't return headers
                \curl_setopt($ch, CURLOPT_HEADER, false);
                
                # Return data after call is made
                \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                # Execute the REST call
                $response = \curl_exec($ch);
                
                if($raw == true)
                {
                    $result = $response;
                }
                else
                {
                    $xml = \simplexml_load_string($response,'SimpleXMLElement', LIBXML_NOCDATA);
                    $json = \json_encode($xml);
                    $result = \json_decode($json, true);
                }
                
                # Close the connection
                curl_close($ch); 
            }
            
            return $result;
        }

        /**
         * @name getOffers
         * @description get offers
         */
        function getOffers($offerIds)
        {
            $offers = [];
            
            if(is_array($offerIds))
            {
                foreach ($offerIds as $offerId)
                {
                    $offers[] = $this->getOffer($offerId);
                }
            }
            
            return $offers;
        }
        
        /**
         * @name getOffer
         * @description get offer by id 
         * @param integer $campaignId
         * @return array the offer
         */
        public function getOffer($campaignId)
        {
            $offer = array();
            
            try
            {
                $parameters = array('api_key' => $this->getKey(),'affiliate_id' => $this->_affiliateId, 'campaign_id' => intval($campaignId));
                $response = $this->getResponse(trim($this->_url,RDS) . RDS . 'offers.asmx' . RDS . 'GetCampaign', $parameters);

                if (count($response)) 
                {   
                    # convert offer data into array 
                    $offerResponse = $response['campaign'];

                    if($response['success'] == 'true' && count($offerResponse) && trim($offerResponse['status_name']) == 'Active')
                    { 
                        # fill the offer 
                        $offer['campaign-id'] = $campaignId;
                        $offer['id'] = $offerResponse['offer_id'];
                        $offer['name'] = $offerResponse['offer_name'];

                        $flags = array();

                        if(key_exists('allowed_countries',$offerResponse))
                        {     
                            $allowedCountries = count($offerResponse['allowed_countries']) == 1 ? array($offerResponse['allowed_countries']) : $offerResponse['allowed_countries'];

                            if(count($allowedCountries))
                            {
                                $countries = key_exists('country_code',$offerResponse['allowed_countries']['country']) ? array($offerResponse['allowed_countries']['country']) : $offerResponse['allowed_countries']['country'];

                                foreach ($countries as $country) 
                                {
                                    $flags[] = $country['country_code'] == 'GB' ? 'UK' : $country['country_code'];
                                }
                            }   
                        }
                        else
                        {
                            if(strpos(strtolower($offerResponse['restrictions']),'uk only') > -1)
                            {
                                $flags[0] = 'UK';
                            }
                            else if(strpos(strtolower($offerResponse['restrictions']),'us only') > -1)
                            {
                                $flags[0] = 'US';
                            }
                            else
                            {
                                $flags[0] = 'ALL';
                            }  
                        }

                        $offer['flag'] = trim(join('/', $flags),'/');
                        $offer['description'] = base64_encode($offerResponse['description']);
                        $offer['rate'] = $offerResponse['payout'];
                        $offer['launch-date'] = date('Y-m-d');
                        $offer['expiring-date'] = date('Y-m-d');
                        $offer['vertical'] = $offerResponse['vertical_name'];
                        $offer['rules'] = base64_encode($offerResponse['restrictions']);
                        $offer['suppression-list-link'] = key_exists('suppression_link', $offerResponse) ? $offerResponse['suppression_link'] : '';
                        $offer['epc'] = $offerResponse['price_format'];
                        $offer['offer_names'] = $offerResponse['from_lines'];
                        $offer['offer_names'] = is_array($offer['offer_names']['string']) ? $offer['offer_names']['string'] : array($offer['offer_names']['string']);
                        $offer['offer_subjects'] = $offerResponse['subject_lines'];
                        $offer['offer_subjects'] = is_array($offer['offer_subjects']['string']) ? $offer['offer_subjects']['string'] : array($offer['offer_subjects']['string']);
                        $offer['key'] = 's2';
                        
                        # getting creatives
                        $offer['creatives'] = $this->getOfferCreatives($offerResponse,$campaignId);
                    } 
                }
            } 
            catch (\SoapFault $e) 
            {
                # log the message error
                Logger::error($e);
            }

            return $offer;
        }
        
        /**
         * @name getOfferCreatives
         * @description get the offer creatives list 
         * @param array $offerResponse
         * @return array the creatives result
         */
        public function getOfferCreatives($offerResponse,$campaignId)
        {
            $results = array();
            
            if(isset($offerResponse) && count($offerResponse) && key_exists('creatives',$offerResponse))
            {
                $creatives = is_array($offerResponse['creatives']['creative_info']) ? $offerResponse['creatives']['creative_info'] : array($offerResponse['creatives']['creative_info']);
                
                foreach ($creatives as $creative) 
                {
                    if(isset($creative['creative_type']) && trim(strtolower($creative['creative_type']['type_name'])) == 'email')
                    {
                        $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'creatives' . DS . Strings::generateRandomText(5,true,true,true,false);
                        $fileName = 'crt_' . Strings::generateRandomText(5,true,true,true,false) . '.zip';

                        # create a temp directory
                        System::executeCommand('mkdir -p ' . $fileDirectory,true);
                        
                        # download the zip file
                        if(filter_var($creative['creative_download_url'],FILTER_VALIDATE_URL) && file_put_contents($fileDirectory . DS . $fileName,file_get_contents($creative['creative_download_url'])))
                        {
                            # unzip the downloaded file 
                            System::executeCommand("unzip " . $fileDirectory . DS . $fileName . " -d " . $fileDirectory . DS,true);
                            
                            $images = glob($fileDirectory."/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
                            $creativeContent = trim(mb_convert_encoding($this->getCreativeContent($campaignId,$creative['creative_id']), "UTF-8"),'?');
                            
                            if(count($images))
                            {
                                foreach ($images as $image) 
                                {
                                    $filePath = trim($image);
                                    $pathParts = pathinfo($filePath);
                                    $extension = key_exists('extension',$pathParts) ? $pathParts['extension'] : 'jpg';
                                    $fileName = Strings::generateRandomText(8,true,false,true,false) . '.' . $extension;

                                    if(!file_exists(ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $fileName))
                                    {
                                        # move the image to our tmp images folder 
                                        System::executeCommand('mv ' . $filePath . ' ' . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $fileName);
                                    }

                                    $creativeContent = str_replace(basename($filePath),URL::getCurrentApplicationURL() . "/tmp/images/" . $fileName, $creativeContent);
                                }
                            }
                            
                            $links = array();
                            $checkLinks = array();

                            # get all links
                            $doc = new \DOMDocument();
                            $doc->loadHTML($creativeContent);
                            $xml = simplexml_import_dom($doc);

                            if($xml != null)
                            {
                                $anchors = $xml->xpath('//a');

                                if (count($anchors))
                                {
                                    foreach ($anchors as $anchor)
                                    {
                                        $link = trim((string) $anchor['href']);

                                        if(filter_var(trim($link), FILTER_VALIDATE_URL) && !in_array(trim($link),$checkLinks))
                                        {
                                            $type = 'other';

                                            if(Strings::indexOf($link,"E=") >= 0 || Strings::indexOf($link,"s1=") >= 0)
                                            {
                                                $type = 'preview';
                                            }
                                            else if(Strings::indexOf($link,"unsub") >= 0 || Strings::indexOf($link,"optout") >= 0 || Strings::indexOf($link,"remove") >= 0)
                                            {
                                                $type = 'unsub';
                                            }

                                            $links[] = array('type' => $type , 'link' => trim($link));
                                            $checkLinks[] = trim($link);
                                        }
                                    }
                                }
                            }

                            $creativeArray['links'] = $links;
                            $creativeArray['code'] = $creativeContent;
                            $results[] = $creativeArray;
                        }
                        
                        # remove the temp directory
                        //System::executeCommand('rm -rf ' . $fileDirectory);
                    }
                }
            }

            return $results;
        }
        
        /**
         * @name getCreativeContent
         * @description get creative content
         * @param integer $campaignId
         * @param integer $creativeId
         * @return array the creatives result
         */
        public function getCreativeContent($campaignId,$creativeId)
        {
            $content = '';  
            $parameters = array('api_key' => $this->getKey(),'affiliate_id' => $this->_affiliateId, 'campaign_id' => intval($campaignId), 'creative_id' => intval($creativeId));
            $response = $this->getResponse(trim($this->_url,RDS) . RDS . 'offers.asmx' . RDS . 'GetCreativeCode', $parameters);

            if (count($response) && key_exists('creative_files',$response) && key_exists('creative_file',$response['creative_files']))
            {
                $content = $response['creative_files']['creative_file']['file_content'];
            }
            
            return $content; 
        }
        
        /**
         * @name getAllVerticals
         * @description get get verticals
         * @return array the verticals result
         */
        public function getAllVerticals()
        {
            $verticalsResult = array();
            
            $parameters = array('api_key' => $this->getKey(),'affiliate_id' => $this->_affiliateId);
            $response = $this->getResponse(trim($this->_url,RDS) . RDS . 'offers.asmx' . RDS . 'GetVerticals', $parameters);
            
            if (count($response) && key_exists('verticals',$response))
            {
                $verticals = $response['verticals']['vertical'];
                
                foreach ($verticals as $vertical) 
                {
                    if(count($vertical) && key_exists('vertical_name',$vertical))
                    {
                        $verticalName = trim($vertical['vertical_name']);
                        
                        if(!in_array($verticalName, $verticalsResult))
                        {
                            $verticalsResult[] = $verticalName;
                        }
                    }
                }
            }
            
            return $verticalsResult; 
        }
        
        /**
         * @name getMonthEarning
         * @description get Month earning
         * @return float earnings
         */
        public function getMonthEarning()
        {
            $earnings = 0.0;
            $parameters = array('api_key' => $this->getKey(),'affiliate_id' => $this->_affiliateId,'date' => date('Y-m-d H:i'));
            $response = $this->getResponse(trim($this->_url,RDS) . RDS . 'reports.asmx' . RDS . 'PerformanceSummary', $parameters);

            if (count($response) && key_exists('periods',$response) && key_exists('period',$response['periods']) && count($response['periods']['period']) > 3 && $response['periods']['period'][3] != null)
            {
                $monthly = $response['periods']['period'][3];
                $earnings += floatval(str_replace("$","",$monthly['current_revenue']));
            }
            
            return $earnings;
        }
    }
}



