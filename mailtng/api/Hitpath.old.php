<?php namespace ma\mailtng\api
{
    if (!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework       MailTng Framework
     * @version         1.1
     * @author         
     * @copyright       Copyright (c) 2015 - 2016.	
     * @license		
     * @link	
     */
    use ma\mailtng\core\Base as Base;
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\www\URL as URL;
    /**
     * @name            Hitpath.class 
     * @description     It's a class that deals with Hitpath methods
     * @package		ma\mailtng\helpers\www
     * @category        Word Wide Web Class
     * @author					
     */
    class Hitpath extends Base
    {
        /**
         * @readwrite
         * @access protected 
         * @var String
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
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_previewURLPrefix = 'pub';
        
        /**
         * @name getResponse
         * @description get Response
         */
        public function getResponse($data,$raw = false)
        {
            $result = [];
            
            if(filter_var($this->_url,FILTER_VALIDATE_URL))
            {
                # Initiate the REST call via curl
                $ch = \curl_init($this->_url);
                \curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0");
                \curl_setopt($ch, CURLOPT_FAILONERROR, true);
                \curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                \curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                \curl_setopt($ch, CURLOPT_VERBOSE, false);
                
                # Set the HTTP method to GET
                \curl_setopt($ch, CURLOPT_POST, true);
                \curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query(array_merge(['apikey' => $this->_key],$data)));
     
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
            $offers = array();
            $response = $this->getResponse(array('apifunc' => 'getcampaigns'));

            if(array_key_exists('data',$response) && count($response['data'])  && is_array($offerIds))
            {
                foreach ($response['data'] as $offerData) 
                {
                    if(is_array($offerData) && count($offerData))
                    {
                        $campaignId = trim(preg_replace('/\s\s+/','',$offerData['campaignid']));
                        
                        if(in_array(intval($campaignId),$offerIds))
                        {
                            $offer = array();
                            $offer['campaign-id'] = $campaignId;
                            $offer['id'] = trim(preg_replace('/\s\s+/','',$offerData['campaignid']));
                            $offer['name'] = trim(preg_replace('/\s\s+/','',$offerData['name']));
                            $offer['description'] = base64_encode(trim(preg_replace('/\s\s+/','',$offerData['description'])));
                            $offer['rate'] = trim(preg_replace('/\s\s+/','',$offerData['payout']));
                            $offer['vertical'] = Arrays::getElement(explode(',',trim(preg_replace('/\s\s+/','',$offerData['category']))),0);
                            $offer['launch-date'] = date('Y-m-d');
                            $offer['expiring-date'] = date('Y-m-d');
                            $offer['rules'] = '';
                            $offer['suppression-list-link'] = '';
                            $offer['epc'] = trim(preg_replace('/\s\s+/','',$offerData['payout']));
                            $offer['flag'] = 'US';
                            $offer['key'] = 'c2';

                            $geoTraffic = trim(preg_replace('/\s\s+/','',$offerData['geotargeting']));

                            if(strtolower($geoTraffic) == 'all traffic accepted')
                            {
                                $offer['flag'] = 'ALL';
                            }
                            else
                            {
                                $geoTrafficParts = explode(' ',str_replace(',','',$geoTraffic));

                                foreach ($geoTrafficParts as $part) 
                                {
                                    if(strlen(trim($part)) == 2 && strtoupper(trim($part)) != 'US')
                                    {
                                        $offer['flag'] .= ',' .  strtoupper(trim($part));
                                    }
                                }
                            }

                            # get subjects and fromnames and creative
                            $offer['creatives'] = $this->getCreatives($offer);
                            $offers[] = $offer; 
                        }  
                    }
                }
            }
 
            return $offers;
        }
        
        /**
         * @name getOffers
         * @description get offers
         */
        function getOffer($offerId)
        {
            $offer = array();
            $response = $this->getResponse(array('apifunc' => 'getcampaigns'));

            if(array_key_exists('data',$response) && count($response['data']))
            {

                foreach ($response['data'] as $offerData) 
                {
                    if(is_array($offerData) && count($offerData))
                    {
                        $campaignId = trim(preg_replace('/\s\s+/','',$offerData['campaignid']));

                        if($offerId != 0 && intval($campaignId) == intval($offerId))
                        {
                            $offer['campaign-id'] = $campaignId;
                            $offer['id'] = $campaignId;
                            $offer['name'] = trim(preg_replace('/\s\s+/','',$offerData['name']));
                            $offer['description'] = base64_encode(trim(preg_replace('/\s\s+/','',$offerData['description'])));
                            $offer['rate'] = trim(preg_replace('/\s\s+/','',$offerData['payout']));
                            $offer['vertical'] = Arrays::getElement(explode(',',trim(preg_replace('/\s\s+/','',$offerData['category']))),0);
                            $offer['launch-date'] = date('Y-m-d');
                            $offer['expiring-date'] = date('Y-m-d');
                            $offer['rules'] = '';
                            $offer['suppression-list-link'] = '';
                            $offer['epc'] = trim(preg_replace('/\s\s+/','',$offerData['payout']));
                            $offer['flag'] = 'US';
                            $offer['key'] = 'c2';
                            
                            $geoTraffic = trim(preg_replace('/\s\s+/','',$offerData['geotargeting']));

                            if(strtolower($geoTraffic) == 'all traffic accepted')
                            {
                                $offer['flag'] = 'ALL';
                            }
                            else
                            {
                                $geoTrafficParts = explode(' ',str_replace(',','',$geoTraffic));

                                foreach ($geoTrafficParts as $part) 
                                {
                                    if(strlen(trim($part)) == 2 && strtoupper(trim($part)) != 'US')
                                    {
                                        $offer['flag'] .= ',' .  strtoupper(trim($part));
                                    }
                                }
                            }

                            # get subjects and fromnames and creative
                            $offer['creatives'] = $this->getCreatives($offer);
                        }
                    }
                }
            }

            return $offer;
        }
    
        /**
         * @name getCreatives
         * @description get creatives
         */
        public function getCreatives(&$offer)
        {
            $creatives = array();
            
            if(count($offer)) 
            {
                $response = $this->getResponse(array('apifunc' => 'getemaillist','campaignid' => $offer['id']));
                
                # get data
                $data = key_exists('type',Arrays::getElement($response,'data')) ? [Arrays::getElement($response,'data')] : Arrays::getElement($response,'data');
                
                foreach ($data as $row) 
                {
                    $id = intval(trim(preg_replace('/\s\s+/','',$row['id'])));
                    $creativeRespone = $this->getResponse(array('apifunc' => 'getemailraw','campaignid' => $offer['id'],'emailid' => $id));
                    
                    if(count($creativeRespone['data']))
                    {
                        $creativeData = $creativeRespone['data'];
                        
                        # add subjects to offer 
                        $subjects = explode(PHP_EOL,$creativeData['subjects']);

                        if(key_exists('offer_subjects',$offer) && is_array($offer['offer_subjects']))
                        {
                            $offer['offer_subjects'] = array_unique(array_merge($offer['offer_subjects'],$subjects));
                        }
                        else
                        {
                            $offer['offer_subjects'] = $subjects;
                        }

                        # add subjects to offer 
                        $fromlines = explode(PHP_EOL,$creativeData['fromlines']);

                        if(key_exists('offer_names',$offer) && is_array($offer['offer_names']))
                        {
                            $offer['offer_names'] = array_unique(array_merge($offer['offer_names'],$fromlines));
                        }
                        else
                        {
                            $offer['offer_names'] = $fromlines;
                        }

                        $creative = array();
                        $creative['code'] = $creativeData['body'];

                        $links = array();
                        $checkLinks = array();
                        $isPreview = false;
                        
                        # get all links
                        $doc = new \DOMDocument();
                        $doc->loadHTML($creative['code']);
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

                                        if(Strings::indexOf($link,$this->_previewURLPrefix) >= 0 && Strings::indexOf($link,strval($this->_affiliateId)) >= 0)
                                        {
                                            $type = 'preview';
                                            $isPreview = true;
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
                        
                        if(count($links) && $isPreview == false)
                        {
                            $preview = $links[0]['link'];
                            
                            foreach ($links as &$linkRow) 
                            {
                                if(trim($linkRow['link']) == trim($preview))
                                {
                                    $linkRow['type'] = 'preview';
                                }
                            }
                        }
                        
                        # get images 
                        $images = key_exists('key',Arrays::getElement($creativeData['images'],'data')) ? [Arrays::getElement($creativeData['images'],'data')] : Arrays::getElement($creativeData['images'],'data');
                        
                        foreach ($images as $image) 
                        {
                            $name = trim(preg_replace('/\s\s+/','',$image['name']));
                            $key = trim(preg_replace('/\s\s+/','',$image['key']));
                            
                            $imageResponse = $this->getResponse(array('apifunc' => 'getemailimage','campaignid' => $offer['id'],'emailid' => $id ,'imagekey' => $key),true);
                            
                            if($imageResponse != null)
                            {
                                $extension = '.' . Arrays::getElement(explode('.',$name),1);
                                $folder = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images';
                                $imageFile = 'img_' . Strings::generateRandomText(8,true,true,true,false) . $extension;

                                # save the file 
                                if(file_put_contents($folder . DS . $imageFile,$imageResponse))
                                {

                                    $creative['code'] = str_replace($name,URL::getCurrentApplicationURL() . RDS . DEFAULT_TEMP_DIRECTORY . RDS . "images/" . $imageFile, $creative['code']);
                                }
                            }  
                        }

                        $creative['links'] = $links;
                        $creatives[] = $creative;
                    }
                }
            }
            
            return $creatives;
        }
        
        /**
         * @name getMonthEarning
         * @description get Month earning
         * @return float earnings
         */
        public function getMonthEarning()
        {
            $earnings = 0.0;
            
            
            return $earnings;
        }
    }
}



