<?php if (!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
/**
 * @framework       MailTng Framework
 * @version         1.1
 * @author          MailTng Team
 * @copyright       Copyright (c) 2015 - 2016.	
 * @license		
 * @link	
 */
use ma\mailtng\database\Database as Database;
use ma\mailtng\logging\Logger as Logger;
use \ma\mailtng\exceptions\types\FatalException as FatalException;
/**
 * @name            error.conf.php 
 * @description     Error configuration file that contains fatal error handling function , it takes uncachable fatal errors and wraps them into a FatalException object 
 * @package         .
 * @category        Config File
 * @author          MailTng Team			
 */

# disables error reporting if the framework is in prod mode
if(defined('ENVIRONEMENT') && ENVIRONEMENT === 'prod')
{
    error_reporting(0);
}

/**
 * @name handleNoticesAndWarnings
 * @description handles notices and warnings and log them
 * @param integer $errorCode the error code (500,404,...)
 * @param string $errorMessage the error message 
 * @param string $errorFile the error file name 
 * @param integer $errorLine the error line 
 * @protected
 */
function handleNoticesAndWarnings($errorCode, $errorMessage, $errorFile, $errorLine)
{
    if (!(error_reporting() & $errorCode)) 
    {
        return;
    }

    switch ($errorCode) 
    {
        case E_USER_WARNING:
        {
            Logger::debug("WARNING: [$errorCode] $errorMessage " .PHP_EOL);
            break;
        } 
        case E_USER_NOTICE:
        {
            Logger::debug("NOTICE: [$errorCode] $errorMessage " .PHP_EOL);
            break;
        }
        default:
        {
            Logger::debug("Unknown error type : [$errorCode] $errorMessage " .PHP_EOL);
            break;
        }
    }

    return true;
}

/**
 * @name handleErrors
 * @description handles fatal errors and wraps them into a FatalException object
 * @protected
 * @throws FatalException
 */
function handleErrors()
{
    # make sure that the database connection is closed
    Database::secureDisconnect();
    
    $errorArray = error_get_last();
    
    if (!$errorArray) 
    {
        # This error code is not included in error_reporting
        return;
    }

    if(is_array($errorArray) && count($errorArray) > 0)
    {
        if($errorArray['type'] && $errorArray['message'] && $errorArray['file'] && $errorArray['line'])
        {
            throw new FatalException($errorArray['message'],$errorArray['type'],null,$errorArray['file'],$errorArray['line']);
        } 
    }
}

# Registering Warnings and Notices Handler
set_error_handler('handleNoticesAndWarnings');

# Registering handleErrors as the shutdown function to do a workaround of error handling
register_shutdown_function('handleErrors');