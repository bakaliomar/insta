<?php namespace ma\mailtng\mail
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
    use ma\mailtng\types\Arrays as Arrays; 
    use ma\mailtng\types\Objects as Objects;
    use ma\mailtng\exceptions\types\ConnectionException as ConnectionException;
    use ma\mailtng\exceptions\types\ArgumentException as ArgumentException;
    /**
     * @name            MailBoxManager.class 
     * @description     It deals with mailbox operations using imap
     * @package		ma\mailtng\metadata
     * @category        Metadata Class
     * @author		MailTng Team			
     */
    class MailBoxManager extends Base
    {
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_ispsParameters = array(
            'yahoo' => array(
                'path' => '{imap.mail.yahoo.com:993/imap/ssl/novalidate-cert}',
                'folders' => array(
                    'inbox' => 'INBOX',
                    'spam' => 'Bulk Mail',
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'trash' => 'Trash'
                )
            ),
            'gmail' => array(
                'path' => '{imap.gmail.com:993/imap/ssl/novalidate-cert}',
                'folders' => array(
                    'inbox' => 'INBOX',
                    'spam' => '[Gmail]/Spam',
                    'draft' => 'Drafts',
                    'sent' => 'Sent',
                    'trash' => 'Trash'
                )
            ),
            'hotmail' => array(
                'path' => '{imap-mail.outlook.com:993/imap/ssl/novalidate-cert}',
                'folders' => array(
                    'inbox' => 'INBOX',
                    'spam' => 'Junk',
                    'draft' => 'Drafts',
                    'sent' => 'Sent',
                    'trash' => 'Deleted'
                )
            ),
            'onmicrosoft.com' => array(
                'path' => '{outlook.office365.com:993/imap/ssl/novalidate-cert}',
                'folders' => array(
                    'inbox' => 'INBOX',
                    'spam' => 'Junk',
                    'draft' => 'Drafts',
                    'sent' => 'Sent',
                    'trash' => 'Deleted'
                )
            ),
            'icloud' => array(
                'path' => '{imap.mail.me.com:993/imap/ssl/novalidate-cert}',
                'folders' => array(
                    'inbox' => 'INBOX',
                    'spam' => 'Junk',
                    'draft' => 'Drafts',
                    'sent' => 'Sent Messages',
                    'trash' => 'Deleted Messages'
                )
            ),
            'aol' => array(
                'path' => '{imap.csi.com:993/imap/ssl/novalidate-cert}',
                'folders' => array(
                    'inbox' => 'INBOX',
                    'spam' => 'Spam',
                    'draft' => 'Drafts',
                    'sent' => 'Sent',
                    'trash' => 'Trash'
                )
            )
        );
        
        /** 
         * @readwrite
         * @access protected 
         * @var ressource
         */ 
        protected $_stream;
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_charset = 'utf-8';
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_isp;
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_folder = 'inbox';
        
        /**
         * @name __construct
         * @description the class constructor
         * @access public
         * @param array $options
         * @return View
         */
        public function __construct($options = array())
        {
            parent::__construct($options);
        }
        
        /**
         * @name connect
         * @description connect to a mailbox by a username and password given 
         * @access public
         * @param string $username
         * @param string $password
         * @param string $isp
         * @param string $folder
         * @return
         */
        public function connect($username,$password)
        {
            if(!isset($username) || !isset($password))
            {
                throw new ArgumentException('Please provide a valid username and password !');
            }
            
            if(!isset($this->_isp) || !array_key_exists($this->_isp,$this->_ispsParameters) || !isset($this->_folder))
            {
                throw new ArgumentException('Please provide a supported isp ! Hint ( Supported Isps ) : ' . implode(',',  array_keys($this->_ispsParameters)));
            }
            
            $path = Arrays::getElement(Arrays::getElement($this->_ispsParameters,$this->_isp),'path');
            $folder = Arrays::getElement(Arrays::getElement(Arrays::getElement($this->_ispsParameters,$this->_isp),'folders'),$this->_folder);
            
            $this->_stream = imap_open($path . $folder,$username,$password);
            
            if(!$this->_stream)
            {
                throw new ConnectionException('Cannot connect to ISP because of : ' . imap_last_error());
            }
        }
        
        /**
         * @name reconnect
         * @description reconnect to a mailbox by a username and password given 
         * @access public
         * @return
         */
        public function reconnect()
        {
            if(!isset($this->_isp) || !array_key_exists($this->_isp,$this->_ispsParameters) || !isset($this->_folder))
            {
                throw new ArgumentException('Please provide a supported isp ! Hint ( Supported Isps ) : ' . implode(',',  array_keys($this->_ispsParameters)));
            }
            
            $path = Arrays::getElement(Arrays::getElement($this->_ispsParameters,$this->_isp),'path');
            $folder = Arrays::getElement(Arrays::getElement(Arrays::getElement($this->_ispsParameters,$this->_isp),'folders'),$this->_folder);

            $this->_stream = imap_reopen($this->_stream,$path . $folder);
            
            if(!$this->_stream)
            {
                throw new ConnectionException('Cannot connect to ISP because of : ' . imap_last_error());
            }
        }
        
        /**
         * @name disconnect
         * @description disconnect from a mailbox
         * @access public
         * @return
         */
        public function disconnect()
        {
            if(is_resource($this->_stream))
            {
                imap_close($this->_stream);
            }
        }
        
        /**
         * @name checkMailbox
         * @description get information about the current mailbox.
         * @access public
         * @return array
         */
        public function checkMailbox()
        {
            return Objects::objectToArray(imap_check($this->_stream));
        }
        
        /**
         * @name getEmailsInfo
         * @description get emails informations
         * @access public
         * @param array $emailIds
         * @return array
         */
        public function getEmailsInfo($emailIds)
        {
            return Objects::objectToArray(imap_fetch_overview($this->_stream,implode(',',$emailIds)));
        }
        
        /**
         * @name getMailboxInfo
         * @description get mailbox informations
         * @access public
         * @param array $emailIds
         * @return array
         */
        public function getMailboxInfo()
        {
            return Objects::objectToArray(imap_mailboxmsginfo($this->_stream));
        }
        
        /**
         * @name getMailboxInfo
         * @description Gets mails ids sorted by some criteria
         * Criteria can be one (and only one) of the following constants:
         * SORTDATE - mail Date
         * SORTARRIVAL - arrival date (default)
         * SORTFROM - mailbox in first From address
         * SORTSUBJECT - mail subject
         * SORTTO - mailbox in first To address
         * SORTCC - mailbox in first cc address
         * SORTSIZE - size of mail in octets
         * @access public
         * @param integer $criteria
         * @param boolean $reverse
         * @return boolean
         */
        public function sortEmails($criteria = SORTARRIVAL, $reverse = true)
        {
            return imap_sort($this->_stream, $criteria, $reverse);
        }
        
        /**
         * @name emailsCount
         * @description get emails count
         * @access public
         * @return integer
         */
        public function emailsCount()
        {
            return imap_num_msg($this->_stream);
        }
        
        /**
         * @name getEmailsIds
         * @description get all emails ids
         * cretira list :
         * ALL - return all mails matching the rest of the criteria
         * ANSWERED - match mails with the \\ANSWERED flag set
         * BCC "string" - match mails with "string" in the Bcc: field
         * BEFORE "date" - match mails with Date: before "date"
         * BODY "string" - match mails with "string" in the body of the mail
         * CC "string" - match mails with "string" in the Cc: field
         * DELETED - match deleted mails
         * FLAGGED - match mails with the \\FLAGGED (sometimes referred to as Important or Urgent) flag set
         * FROM "string" - match mails with "string" in the From: field
         * KEYWORD "string" - match mails with "string" as a keyword
         * NEW - match new mails
         * OLD - match old mails
         * ON "date" - match mails with Date: matching "date"
         * RECENT - match mails with the \\RECENT flag set
         * SEEN - match mails that have been read (the \\SEEN flag is set)
         * SINCE "date" - match mails with Date: after "date"
         * SUBJECT "string" - match mails with "string" in the Subject:
         * TEXT "string" - match mails with text "string"
         * TO "string" - match mails with "string" in the To:
         * UNANSWERED - match mails that have not been answered
         * UNDELETED - match mails that are not deleted
         * UNFLAGGED - match mails that are not flagged
         * UNKEYWORD "string" - match mails that do not have the keyword "string"
         * UNSEEN - match mails which have not been read yet
         * @access public
         * @param string $criteria
         * @return array
         */
        public function getEmailsIds($criteria = 'ALL')
        {
            return imap_search($this->_stream,$criteria);
        }
        
        /**
         * @name getEmailHeader
         * @description get email header
         * @access public
         * @param integer $emailId
         * @return string
         */
        public function getEmailHeader($emailId)
        {
            return imap_fetchheader($this->_stream,$emailId);
        }
        
        /**
         * @name getEmail
         * @description get email by eid
         * @access public
         * @param integer $emailId
         * @return array
         */
        public function getEmail($emailId)
        {
            $email = array();
            
            # get headers
            $headers = Objects::objectToArray(imap_rfc822_parse_headers(imap_fetchheader($this->_stream,$emailId)));

            $email['id'] = $emailId;
            $email['date'] = date('Y-m-d H:i:s', isset($headers['date']) ? strtotime($headers['date']) : time());
            $email['subject'] = $this->decodeMimeString($headers['subject']);
            $email['from-name'] = isset($headers['from'][0]->personal) ? $this->decodeMimeString($headers['from'][0]->personal) : null;
            $email['from-email'] = strtolower($headers['from'][0]->mailbox . '@' . $headers['from'][0]->host);
 
            $toValues = array();
            
            foreach ($headers['to'] as $to) 
            {
                if (!empty($to->mailbox) && !empty($to->host)) 
                {
                    $toEmail = strtolower($to->mailbox . '@' . $to->host);
                    $toName = isset($to->personal) ? $this->decodeMimeString($to->personal) : null;
                    $toValues[] = $toName ? "$toName <$toEmail>" : $toEmail;
                    $email['to'][$toEmail] = $toName;
                }
            }
        
            $email['to-values'] = implode(', ', $toValues);

            if (isset($headers['cc'])) 
            {
                foreach ($headers['cc'] as $cc) 
                {
                    $email['cc'][strtolower($cc->mailbox . '@' . $cc->host)] = isset($cc->personal) ? $this->decodeMimeString($cc->personal) : null;
                }
            }

            if (isset($headers['reply_to'])) 
            {
                foreach ($headers['reply_to'] as $replyTo) 
                {
                    $email['reply-to'][strtolower($replyTo->mailbox . '@' . $replyTo->host)] = isset($replyTo->personal) ? $this->decodeMimeString($replyTo->personal) : null;
                }
            }

            $mailStructure = imap_fetchstructure($this->_stream,$emailId);
            
            if (empty($mailStructure->parts)) 
            {
                $this->initializeBodyPart($email, $mailStructure, 0);
            } 
            else 
            {
                foreach ($mailStructure->parts as $partNum => $partStructure) 
                {
                    $this->initializeBodyPart($email, $partStructure, $partNum + 1);
                }
            }
        
            return $email;
        }
        
        /**
         * @name moveEmails
         * @description move emails by ids to another folder
         * @access public
         * @param array $emailIds
         * @return boolean
         */
        public function moveEmails($emailIds,$folder = 'inbox')
        {
            $folder = Arrays::getElement(Arrays::getElement(Arrays::getElement($this->_ispsParameters,$this->_isp),'folders'),$folder);
            return imap_mail_move($this->_stream, implode(',',$emailIds),$folder);
        }
        
        /**
         * @name moveMails
         * @description move emails by ids to another folder
         * @access public
         * @param integer $emailId
         * @return boolean
         */
        public function deleteEmail($emailId)
        {
            return imap_delete($this->_stream,$emailId);
        }
 
        /**
         * @name markEmailsAsRead
         * @description mark emails by ids as read
         * @access public
         * @param array $emailIds
         * @return boolean
         */
        public function markEmailsAsRead($emailIds)
        {
            return $this->setFlagForEmails($emailIds, '\\Seen');
        }
        
        /**
         * @name markEmailsAsRead
         * @description mark emails by ids as read
         * @access public
         * @param array $emailIds
         * @return boolean
         */
        public function markEmailsAsUnRead($emailIds)
        {
            return $this->clearFlagFromEmails($emailIds, '\\Seen');
        }
        
        /**
         * @name markMailAsImportant
         * @description mark emails by ids as important
         * @access public
         * @param array $emailIds
         * @return boolean
         */
        public function markMailAsImportant($emailIds)
        {
            return $this->setFlagForEmails($emailIds, '\\Flagged');
        }
        
        /**
         * @name setFlagForEmails
         * @description add the specified flag to the flags set for the mails in the specified sequence.
         * @access public
         * @param array $emailIds
         * @param string $flag
         * @return boolean
         */
        public function setFlagForEmails($emailIds,$flag)
        {
            return imap_setflag_full($this->_stream , implode(',', $emailIds), $flag);
        }
        
        /**
         * @name clearFlagFromEmails
         * @description clear the specified flag from the flags set for the mails in the specified sequence.
         * @access public
         * @param array $emailIds
         * @param string $flag
         * @return boolean
         */
        public function clearFlagFromEmails($emailIds,$flag)
        {
            return imap_clearflag_full($this->_stream , implode(',', $emailIds), $flag);
        }
        
        /**
         * @name decodeMimeString
         * @description decode headers mime string
         * @access public
         * @param array $emailIds
         * @param string $string
         * @param string $charset
         * @return boolean
         */
        protected function decodeMimeString($string)
        {
            $value = '';
            $elements = imap_mime_header_decode($string);
            for ($i = 0; $i < count($elements); $i++) 
            {
                if ($elements[$i]->charset == 'default') 
                {
                    $elements[$i]->charset = 'iso-8859-1';
                }
                
                $value .= iconv(strtoupper($elements[$i]->charset),$this->_charset, $elements[$i]->text);
            }
            return $value;
        }
        
        /**
         * @name initializeBodyPart
         * @description initialize the body part of the email
         * @access public
         * @param array $email
         * @param string $partStructure
         * @param integer $partNumber
         * @return boolean
         */
        protected function initializeBodyPart(&$email,$partStructure,$partNumber) 
        {
            $parameters = array();
            $data = $partNumber ? imap_fetchbody($this->_stream,$email['id'],$partNumber) : imap_body($this->_stream,$email['id']);
            
            if ($partStructure->encoding == 1) 
            {
                $data = imap_utf8($data);
            } 
            elseif ($partStructure->encoding == 2) 
            {
                $data = imap_binary($data);
            } 
            elseif ($partStructure->encoding == 3) 
            {
                $data = imap_base64($data);
            } 
            elseif ($partStructure->encoding == 4) 
            {
                $data = imap_qprint($data);
            }
            
            if (!empty($partStructure->parameters)) 
            {
                foreach ($partStructure->parameters as $param) 
                {
                    $parameters[strtolower($param->attribute)] = $param->value;
                }
            }
            
            if (!empty($partStructure->dparameters)) 
            {
                foreach ($partStructure->dparameters as $param) 
                {
                    $params[strtolower($param->attribute)] = $param->value;
                }
            }
            
            if (!empty($params['charset'])) 
            {
                $data = iconv($params['charset'],$this->_charset, $data);
            }
        
            if ($partStructure->type == 0 && $data) 
            {
                if (strtolower($partStructure->subtype) == 'plain') 
                {
                    $email['body-text'] .= $data;
                } 
                else 
                {
                    $email['body-html'] .= $data;
                }
            } 
            elseif ($partStructure->type == 2 && $data) 
            {
                $email['body-text'] .= trim($data);
            }
            
            if (!empty($partStructure->parts)) 
            {
                foreach ($partStructure->parts as $subPartNum => $subPartStructure) 
                {
                    $this->initializeBodyPart($email,$subPartStructure,$partNumber . '.' . ($subPartNum + 1));
                }
            }
        }
    
        /**
         * @name __destruct
         * @description a deconstructor that will call the disconnect method automatiqualy
         * @access protected
         * @return
         */
        public function __destruct()
        {
            if (!error_get_last()) 
            {
               $this->disconnect();
            }
        }
    }
}