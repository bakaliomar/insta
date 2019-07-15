<?php namespace ma\mailtng\exceptions
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
    use ma\mailtng\registry\Packager as Packager;
    use ma\mailtng\http\Response as Response;
    use ma\mailtng\http\Session as Session;
    use ma\mailtng\logging\Logger as Logger;
    use ma\mailtng\types\Objects as Objects;
    /**
     * @name            BaseException.class 
     * @description     It's core exception class that all the other exception classes extends from it has a method that displays the error page 
     * @package		ma\mailtng\exceptions
     * @category        Exception Class
     * @author		MailTng Team			
     */
    class BaseException extends \Exception
    { 
        /**
         * @name __construct
         * @description the class constructor
         * @access public
         * @param string $message
         * @param integer $code
         * @param mixed $previous
         * @param string $file
         * @param string $line
         * @return BaseException
         */
        public function __construct($message = '',$code = 500,$previous = null,$file = '',$line = '')
        {
            # check if the message is null and the $previous is an object we get the message from the previous
            $message = ($message === null || $message === '') && (isset($previous) && is_object($previous)) ? $previous->getMessage() : $message;
           
            # calling the parent constructor
            parent::__construct($message,$code,$previous); 
            
            # set the file and the line
            $this->file = $file != null && $file != "" ? $file : $this->file;
            $this->line = $line != null ? $line : $this->line;

            # log the message error
            Logger::error($this);
            
            # render the error
            $this->renderErrorPage(); 
        }

        /**
         * @name renderErrorPage
         * @description this method is to display the error message ( page or just normal message )
         * @access public
         * @return
         */
        public function renderErrorPage()
        {
            try
            {    
                $code = $this->getCode(); 
                # Defining header status
                $headerCode = ($code >= 400 && $code <= 500) ? $code : 500;
                Response::header($headerCode);
                
                # define that if the error page from the handler is shown or not 
                $pageShown = false;
                
                # check if the current application has defined its own error handler 
                $application = Packager::get('application');
                
                # forcing the controller to display an error page instead of the requested one
                $controller = Packager::get("controller");
                
                if(isset($controller))
                {
                    # forcing the controller to display an error page instead of the requested one
                    $controller->setShowMasterView(false);
                    $controller->setShowPageView(false);
                }
                
                if(isset($application) && is_object($application))
                {
                    $settings = $application->getSetting('init');
                    $applicationSettings = Objects::objectToArray($settings);
                    
                    if(isset($applicationSettings) && is_array($applicationSettings) &&  array_key_exists('application_error_controller',$applicationSettings))
                    {
                        $errorHandler = $applicationSettings['application_error_controller'];
                        $applicationPrefix = $applicationSettings['prefix'];
                        
                        if(isset($errorHandler) && $errorHandler != '')
                        {
                            # create the controller object 
                            $class = FW_VENDOR . ANS . DEFAULT_APPS_DIRECTORY . ANS . $applicationPrefix . ANS . DEFAULT_CONTROLLERS_DIRECTORY . ANS . ucfirst($errorHandler); 

                            # check if this error handler has an existant class
                            if(class_exists($class))
                            {
                                $errorHandlerClass = new $class();
                                
                                if (isset($errorHandlerClass) && is_object($errorHandlerClass))
                                {
                                    Session::set("mailtng-error-exception",$this);
                                    $errorHandlerClass->showErrorPage();
                                    $pageShown = true;
                                }
                            }
                        }
                    }
                }

                if($pageShown == false)
                {
                    echo("<pre>Oops!! something went wrong !<br/><br/><span style='color:red'>{$this->getMessage()}</span></pre>");  
                }
                
                # exiting from the script
                exit(0);
            }
            catch (\Exception $exception)
            {
                die('<pre>' . $this->getExceptionMessageString($exception) . '</pre>');
            }
        }

        /**
         * @name getExceptionMessageString
         * @description this method is to get the error message
         * @access public
         * @return string
         */
        public function getExceptionMessageString($exception)
        {
            return sprintf('%s [ %s ]: %s ~ %s [ %d ]',get_class($exception), $exception->getCode(), strip_tags($exception->getMessage()), $exception->getFile(), $exception->getLine());
        }

    }
}

