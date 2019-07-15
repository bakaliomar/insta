<?php
/**
 * @framework       MailTng Framework
 * @version         1.1
 * @author          MailTng Team
 * @copyright       Copyright (c) 2015 - 2016.	
 * @license		
 * @link	
 */ 
use ma\mailtng\types\Strings as Strings;
use ma\mailtng\types\Arrays as Arrays;
use ma\mailtng\encryption\Crypto as Crypto;
use ma\mailtng\www\Domains as Domains;
use ma\mailtng\ssh2\SSH as SSH;
use ma\mailtng\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
use ma\mailtng\pmta\PmtaMailMergeFileMaker as PmtaMailMergeFileMaker;
use ma\mailtng\pmta\PmtaMailMergeSection as PmtaMailMergeSection;
/**
 * @name            mailing_delivery_helper.php 
 * @description     a helper script that contains some usefull methods 
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */

# Mailing Delivery Helper Methods 

/**
* @name buildMailMergeSection
* @description build mail merge section
* @param array $drop
* @param array $client
* @return PmtaMailMergeSection
*/
function buildMailMergeSection(&$drop,$ip,$client)
{ 
    $pmtaMailMergeSection = null;
    
    if(filter_var($client['email'],FILTER_VALIDATE_EMAIL))
    {
        if(count($ip))
        {
            $pmtaMailMergeSection  = new PmtaMailMergeSection();    
            $pmtaMailMergeSection->setVmta(Arrays::getElement($ip,'value'));
            
            $rcpt = $client['email'];
        
            # auto response section
            if($drop['auto-response'] != null && $drop['auto-response'] != '' && $drop['auto-response'] != 'off')
            {
                if($drop['auto-reply-rotator'] != null)
                {
                    $rcpt = $drop['auto-reply-rotator']->getCurrentValue();
                    $drop['auto-reply-rotator']->rotate();
                }
            }
            
            # replace some predefined fields
            $returnPath = replaceTags($drop['return-path'], $ip, $drop, $client);
            $fromEmail = replaceTags($drop['from-email'], $ip, $drop, $client);
            $replyTo = replaceTags($drop['reply-to'], $ip, $drop, $client);
            $to = replaceTags($drop['to'], $ip, $drop, $client);
            $received = replaceTags($drop['received'], $ip, $drop, $client,$returnPath);
            
            $pmtaMailMergeSection->setFrom($returnPath);
            $pmtaMailMergeSection->setRcptTo($rcpt);
            $pmtaMailMergeSection->addXDFN('rcpt',$rcpt);
            $pmtaMailMergeSection->addXDFN('mail_date',date(DATE_RFC2822));
            $pmtaMailMergeSection->addXDFN('message_id',Strings::generateRandomText(12,true,false,true,false));
            $pmtaMailMergeSection->addXDFN('ip',Arrays::getElement($ip,'value'));
            $pmtaMailMergeSection->addXDFN('rdns',Arrays::getElement($ip,'rdns'));
            $pmtaMailMergeSection->addXDFN('domain',Domains::getDomainFromURL(Arrays::getElement($ip,'rdns')));
            $pmtaMailMergeSection->addXDFN('server',Arrays::getElement($drop['server'],'name'));
            $pmtaMailMergeSection->addXDFN('email',$client['email']);
            $pmtaMailMergeSection->addXDFN('email_name',Arrays::getElement(explode('@',$client['email']),0));
            $pmtaMailMergeSection->addXDFN('from_email',$fromEmail);
            $pmtaMailMergeSection->addXDFN('return_path',$returnPath);
            $pmtaMailMergeSection->addXDFN('from_name',$drop['from-name']['value']);
            $pmtaMailMergeSection->addXDFN('subject',$drop['subject']['value']);
            $pmtaMailMergeSection->addXDFN('reply_to',$replyTo);
            $pmtaMailMergeSection->addXDFN('to',$to);
            $pmtaMailMergeSection->addXDFN('received',$received);
            $pmtaMailMergeSection->addXDFN('content_transfer_encoding',$drop['content-transfer-encoding']);
            $pmtaMailMergeSection->addXDFN('content_type',$drop['content-type']);
            $pmtaMailMergeSection->addXDFN('charset',$drop['charset']);
            $pmtaMailMergeSection->addXDFN('drp_meta',intval($drop['drop-id']) . '|' . Arrays::getElement($ip,'id'));
            
            if($drop['body-placeholders-rotator'] != null)
            {
                $pmtaMailMergeSection->addXDFN('placeholder',preg_replace( "/\r|\n/","",$drop['body-placeholders-rotator']->getCurrentValue()));
                $drop['body-placeholders-rotator']->rotate();
            }

            if (count($drop['available-random-tags']))
            {
                foreach ($drop['available-random-tags'] as $tag) 
                {
                    $pmtaMailMergeSection->addXDFN(str_replace(array('[',']'),array('',''),$tag),replaceRandomTag($tag));
                }
            } 
        }

        # generate links 
        generateLinks($drop,$pmtaMailMergeSection,$client,$client['email']);
        
        # rotate the random body placeholders
        $drop['body-placeholders-rotator']->rotate();

        return $pmtaMailMergeSection;
    }
    
    return $pmtaMailMergeSection;
}

/**
* @name createPickupContent
* @description create pickup content
* @return string
*/
function createPickupContent(&$drop,$ip,$pmtaMailMergeSection)
{
    $open = ($drop['track-opens'] == 'on') ? PHP_EOL . '[OPEN]' : '';
    $pmtaFileMaker = new PmtaMailMergeFileMaker();
    $pmtaFileMaker->setXmrgFrom('<' . replaceTags($drop['bounce-email'],$ip,$drop) . '>');
    
    if(is_array($pmtaMailMergeSection))
    {
        $pmtaFileMaker->setPmtaMailMergeSections($pmtaMailMergeSection);
    }
    else
    {
        $pmtaFileMaker->addPmtaMailMergeSection($pmtaMailMergeSection);
    }
    
    # replace negative if any 
    if(Strings::indexOf('[negative]',$drop['message-body']) > -1)
    {
        $drop['message-body'] = str_replace('[negative]',$drop['negative'],$drop['message-body']);
    }
    
    $pmtaFileMaker->setMessage($drop['message-body']);  
    $header = $drop['headers-rotator']->getCurrentValue();
#    $content = $pmtaFileMaker->createPickupFile(null,true,$header . PHP_EOL . 'Drop-Meta : [drp_meta]' . PHP_EOL,0);
    $content = $pmtaFileMaker->createPickupFile(null,true,$header . PHP_EOL,0);
    $drop['headers-rotator']->rotate();
    
    return $content;
}

/**
* @name replaceRandomTag
* @description replace a random tag
* @param array $foundTags
* @param string $value
* @return string
*/
function replaceFoundRandomTag($foundTags,$value)
{
   $result = $value;

   if(count($foundTags) && isset($value))
   {
       foreach ($foundTags as $tag) 
       {
           $result = str_replace($tag,replaceRandomTag($tag),$result);
       }
   }

   return $result;
}

/**
* @name replaceTags
* @description replace tags
* @param string $value
* @return string
*/
function replaceTags($value,$ip,$drop,$client = null,$returnPath = '')
{
    $value = str_replace(['[ip]','[rdns]','[domain]','[mail_date]','[message_id]','[return_path]'], [Arrays::getElement($ip, 'value'),Arrays::getElement($ip, 'rdns'),Domains::getDomainFromURL(Arrays::getElement($ip, 'rdns')),date(DATE_RFC2822),Strings::generateRandomText(12,true,false,true,false),$returnPath], $value);
    
    if($client != null)
    {
        $value = str_replace(['[email]','[email_name]'],[$client['email'],Arrays::getElement(explode('@',$client['email']),0)],$value);
    }
    
    if (count($drop['available-random-tags']))
    {
        foreach ($drop['available-random-tags'] as $tag) 
        {
            if(Strings::indexOf($value,$tag) > -1)
            {
                $value = str_replace($tag,replaceRandomTag($tag),$value);
            }
        }
    }
    
    return $value;
}

/**
* @name replaceRandomTag
* @description replace a random tag
* @param string $tag
* @return string
*/
function replaceRandomTag($tag)
{
   if(isset($tag))
   {
       $lenghtMatch = array();
       $tagName = str_replace(array('[',']'),array('',''),$tag); 

       preg_match('/\d+/',$tagName,$lenghtMatch);

       if(count($lenghtMatch))
       {
           $lentgh = $lenghtMatch[0];
           $type = str_replace($lentgh,"",$tagName); 

           switch ($type) 
           {
               case 'a':
               {
                   return Strings::generateRandomText($lentgh,true,true,false,false);
               }
               case 'al':
               {
                   return strtolower(Strings::generateRandomText($lentgh,true,true,false,false));
               }
               case 'au':
               {
                   return strtoupper(Strings::generateRandomText($lentgh,true,true,false,false));
               }
               case 'an':
               {
                   return Strings::generateRandomText($lentgh,true,true,true,false);
               }
               case 'anl':
               {
                   return strtolower(Strings::generateRandomText($lentgh,true,true,true,false));
               }
               case 'anu':
               {
                   return strtoupper(Strings::generateRandomText($lentgh,true,true,true,false));
               }
               case 'n':
               {
                   return Strings::generateRandomText($lentgh,false,false,true,false);
               }
           }
       }
   }

   return $tag;
}

/**
* @name gatherRandomTags
* @description check for random tags
* @param string $content
* @return string
*/
function gatherRandomTags($content)
{
   $randomTags = array();

   if(!empty($content))
   {
       $matches = array();
       $lenghtMatch = array(); 

       preg_match_all('/\[(.*?)\]/',$content, $matches);
       
       if(count($matches) > 1)
       {
           if(count($matches[1]))
           {
               foreach ($matches[1] as $match) 
               {        
                   preg_match('/\d+/',$match,$lenghtMatch);

                   if(count($lenghtMatch))
                   {
                       $lentgh = $lenghtMatch[0];
                       $type = str_replace($lentgh,"",$match); 

                       if(in_array($type,array('a','al','au','an','anl','anu','n')))
                       {
                           $randomTags[] = "[$match]";
                       }
                   }
               }
           }
       }
   }

   return $randomTags;
}

/**
 * @name generateLinks
 * @description generate links
 * @param array $drop
 * @param object $pmtaMailMergeSection
 * @param array $client
 * @return array
 */
function generateLinks($drop,$pmtaMailMergeSection,$client,$email)
{
   if($pmtaMailMergeSection != null)
   {
        $metadata = $drop['drop-id'] . '|' . $client['table'] . '|' . $client['id'] . '|' . $email;

        switch ($drop['link-encoding']) 
        {
           case 'default' :
           {
                foreach ($drop['links'] as $link) 
                {
                    if(count($link))
                    {
                        $tag = strtolower($link['type']) == 'preview' ? 'url' : 'unsub'; 
                        
                        # add and XDFN with the new link 
                        $pmtaMailMergeSection->addXDFN($tag,'t?v=' . urlencode(Crypto::AESEncrypt(strtolower($link['type']) . '|' . $metadata . '|' . $link['value'])));
                    }
                }
                if($drop['track-opens'] == 'on')
                {
                    # create image of 1px X 1px
                   // $image = "<img alt='' src='http://[domain]/" . 't?v=' . urlencode(Crypto::AESEncrypt('open|' . $metadata)) . "' width='1px' height='1px' style='visibility:hidden'/>";
                   //  $image = 't?v=' . urlencode(Crypto::AESEncrypt('open|' . $metadata));

                    # add and XDFN with the new link 
                  //  $pmtaMailMergeSection->addXDFN('open',$image);
				    $pmtaMailMergeSection->addXDFN('open','t?v=' . urlencode(Crypto::AESEncrypt('open|' . $metadata)));

                }
                
                # add unsubscribe link
                $pmtaMailMergeSection->addXDFN('optout','unsub?m=' . urlencode(Crypto::AESEncrypt($metadata)));
           }
       }
   }
}

/**
 * @name replaceAndUploadImages
 * @description replace and upload images
 * @param array $drop
 * @return
 */
function replaceAndUploadImages(&$drop)
{
   if($drop != null)
   {
        $body = $drop['message-body'];
        
        if(isset($body) && trim($body) != '' && strlen($body) > 0)
        {
            # get all images links
            $doc = new \DOMDocument();
            $doc->loadHTML($body);
            $xml = simplexml_import_dom($doc);
            
            if($xml != null)
            {
                $images = $xml->xpath('//img');
            
                if (count($images))
                {
                    $redirectImagesLink = 'http://[RDNS]' . RDS . 'web' . RDS . 'imgs' . RDS;
                    
                    if(count($drop['server']))
                    {
                        $sshAuthenticator = new SSHPasswordAuthentication($drop['server']['username'],$drop['server']['password']);
                        $sshConnector = new SSH($drop['server']['main_ip'],$sshAuthenticator,$drop['server']['ssh_port']);

                        if($sshConnector->isConnected())
                        {
                            foreach ($images as $img)
                            {
                                $link = (string) $img['src'];

                                # create a name for the image 
                                $name = Strings::generateRandomText(15,true,false,true,false);
                                $extension = pathinfo($link, PATHINFO_EXTENSION);

                                # upload the image 
                                $content = file_get_contents($link);

                                if(!empty($content))
                                {
                                    $sshConnector->scp('send',array('/var/mailtng/web/imgs/' . $name . '.' . $extension),$content);

                                    $imageLink = $redirectImagesLink . $name . '.' . $extension;

                                    # replace the image inside the content
                                    $body = str_replace($link,$imageLink,$body);
                                } 
                            }           
                        } 
                    }
                }
            }

           $drop['message-body'] = $body;
        }
   }
}