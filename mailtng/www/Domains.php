<?php namespace ma\mailtng\www
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
     * @name            Domains.class 
     * @description     It's a class that deals with domains functions 
     * @package		ma\mailtng\www
     * @category        Word Wide Web Class
     * @author		MailTng Team			
     */
    class Domains extends Base
    {
        /**
         * @name getDomainFromURL
         * @description get domain from URL
         * @access static
         * @param string $url
         * @return mixed
         */
        public static function getDomainFromURL($url)
        {
            $url = strpos($url,'http') > -1 ? $url : 'http://' . $url;
            $pieces = parse_url($url);
            $domain = isset($pieces['host']) ? $pieces['host'] : '';
            $regs = array();
            
            if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) 
            {
                return $regs['domain'];
            }
            
            return false;
        }
        
        /**
         * @name isValidDomain
         * @description checks if a given domain a valid one or not
         * @access static
         * @param string $domain
         * @return boolean
         */
        public static function isValidDomain($domain)
        {
            if(stripos($domain, 'http://') === 0)
            {
                $domain = substr($domain, 7);
            }

            if(!substr_count($domain, '.'))
            {
                return false;
            }

            if(stripos($domain, 'www.') === 0)
            {
                $domain = substr($domain, 4);
            }

            $again = 'http://' . $domain;
            return filter_var ($again, FILTER_VALIDATE_URL);
        }

        /**
         * @name isTimedOut
         * @description checks if a domain is timed out or not
         * @access static
         * @param string $domain 
         * @param integer $timeout 
         * @return boolean
         */
        public static function isTimedOut($domain, $timeout = 10) 
        {
            //initialize curl
            $curlInit = curl_init($domain);
            curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,$timeout);
            curl_setopt($curlInit,CURLOPT_HEADER,true);
            curl_setopt($curlInit,CURLOPT_NOBODY,true);
            curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

            //get answer
            $response = curl_exec($curlInit);
            
            curl_close($curlInit);

            if ($response) return false;

            return true;
        }

        /**
         * @name isPT
         * @description check if a URL is valid/online phishing website according to PhishTank
         * @access static
         * @param string $url 
         * @param string $ptkey 
         * @return boolean
         */
        public static function isPT($domain, $ptkey) 
        {
            # PhishTank expects the domain that you are checking to be URL encoded
            $domain = urlencode($domain);

            # Perform an HTTP POST request to PhishTank including the encoded url to get a JSON response using your application/developer key
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, 'http://checkurl.phishtank.com/checkurl/');
            curl_setopt($c, CURLOPT_POST, 1);
            curl_setopt($c, CURLOPT_POSTFIELDS, "format=json&app_key=$ptkey&url=$domain");
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_USERAGENT, 'GAW.SH URL Shortener - http://gaw.sh/');
            curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($c, CURLOPT_TIMEOUT, 2);
            $r = curl_exec($c);
            curl_close($c);

            # If the URL is in PhishTanks database, it is a valid and online phishing website
            if (preg_match('/"in_database":true/', $r)) 
            {
                return TRUE;
            } 
            else 
            {
                return FALSE;
            }
        }

        /**
         * @name isGSB
         * @description check if a domain is listed on the Google safe browsing API which includes phishing/malware URLs
         * @access static
         * @param string $domain 
         * @param string $gsbkey 
         * @return boolean
         */
        public static function isGSB($domain, $gsbkey) 
        {
            # Append the encoded domain that we are checking to the Google Safe Browsing API lookup URL
            $gsburl = 'https://sb-ssl.google.com/safebrowsing/api/lookup?client=gawsh&apikey=' . $gsbkey . '&appver=1.5.2&pver=3.0&url=' . urlencode($domain);

            # Perform an HTTP GET request to the Google Safe Browsing API and make a decision based on response code
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $gsburl);
            curl_setopt($c, CURLOPT_HEADER, 1);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_NOBODY, 1);
            curl_setopt($c, CURLOPT_USERAGENT, 'GAW.SH URL Shortener - http://gaw.sh/');
            curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($c, CURLOPT_TIMEOUT, 2);
            curl_exec($c);
            $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
            curl_close($c);

            # A 200 HTTP response code indicates that the website is involved with phishing or malware
            if ($code == '200') 
            {
                return TRUE;
            } 
            else 
            {
                return FALSE;
            }
        }

        /**
         * @name isDBL
         * @description check if a domain is on Spamhaus' DBL
         * @access static
         * @param string $domain 
         * @return boolean
         */
        public static function isDBL($domain) 
        {
            if(self::isValidDomain($domain))
            {
                # get the page content from spamhaus that checks if the domain is listed or not
                $ch = curl_init();
                $timeout = 5;
                curl_setopt($ch, CURLOPT_URL,"https://www.spamhaus.org/query/domain/$domain");
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $data = curl_exec($ch);
                curl_close($ch);

                # check if listed or not 
                if(strpos($data,$domain.' is listed in the DBL') != false)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }

        /**
         * @name isSURBL
         * @description check if a domain is on SURBL
         * @access static
         * @param string $domain 
         * @return boolean
         */
        public static function isSURBL($domain) 
        {
            # Append ".multi.surbl.org" to the domain name and look it up
            $domain .= '.multi.surbl.org';
            $lookup = gethostbyname($domain);

            # Check the domain name in question against SURBL
            if ($lookup == $domain) 
            {
                return FALSE;
            } 
            else 
            {
                return TRUE;
            }
        }

        /**
         * @name isURIBL
         * @description check if a domain is on URIBL
         * @access static
         * @param string $domain 
         * @return boolean
         */
        public static function isURIBL($domain) 
        {
            # Append ".multi.uribl.com" to the domain name and look it up
            $domain .= '.multi.uribl.com';
            $lookup = gethostbyname($domain);

            # Check the domain name in question against URIBL
            if ($lookup == $domain) 
            {
                return FALSE;
            } 
            else 
            {
                return TRUE;
            }
        }

        /**
         * @name isZEN
         * @description check if a domain resolves to an IP address on Spamhaus' ZEN
         * @access static
         * @param string $domain 
         * @return boolean
         */
        public static function isZEN($domain) 
        {
            # Resolve the domain name to an IPv4 address
            $lookups = dns_get_record($domain, DNS_A);

            # Loop through each IP address returned
            foreach ($lookups as $lookup) 
            {

                # Reverse the octet order of the IP address, append ".zen.spamhaus.org", and look it up
                $checkname = implode('.', array_reverse(explode('.', $lookup['ip']))) . '.zen.spamhaus.org';
                $check = gethostbyname($checkname);

                # Check the IP address in question against Spamhaus' ZEN; ignore 127.0.0.10-11 IPs (PBL)
                if (($check != $checkname) && ($check != '127.0.0.10') && ($check != '127.0.0.11')) 
                {
                    return TRUE;
                }
            }
        }

        /**
         * @name isDumb
         * @description check if a domain name is dumb
         * @access static
         * @param string $domain 
         * @return boolean
         */
        public static function isDumb($domain) 
        {
            # Create an array of dumb domain names from file
            $dumbfile = $_SERVER['DOCUMENT_ROOT'] . 'admin/dumb.txt';
            $dumb = file($dumbfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            # Check the domain name in question against list of dumb domains
            if (array_search(strtolower($domain), $dumb)) 
            {
                return TRUE;
            }
        }

        /**
         * @name isLegit
         * @description check if a domain name is legit
         * @access static
         * @param string $domain 
         * @return boolean
         */
        public static function isLegit($domain) 
        {
            # Hit the URL with an HTTP request using cURL to make sure it connects/works
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $domain);
            curl_setopt($c, CURLOPT_HEADER, 1);
            curl_setopt($c, CURLOPT_NOBODY, 1);
            curl_setopt($c, CURLOPT_USERAGENT, 'GAW.SH URL Shortener - http://gaw.sh/');
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($c, CURLOPT_TIMEOUT, 2);
            curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0); # Do not fail on "invalid" SSL certificates
            curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
            url_exec($c);
            $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
            curl_close($c);

            # As long as the URL works and does not return 404/Not Found, it is legit
            if (($code != '0') && ($code != '404')) 
            {
                return TRUE;
            } 
            else 
            {
                return FALSE;
            }
        }   
    }
}