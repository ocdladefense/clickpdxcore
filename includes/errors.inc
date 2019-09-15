<?php

if(!defined('NOTIFICATION_EMAIL'))
{
	define('NOTIFICATION_EMAIL', 'jbernal.web.dev@gmail.com');
}

if(!defined('EMAIL_ERRORS'))
{
	define('EMAIL_ERRORS', false);
}

define('ALSO_EXECUTE_PHP_ERROR_HANDLER',true);

define('PHP_SYSTEM_LOGGING',0);

define('SAPI_SYSTEM_LOGGING',4);

define('EMAIL_LOGGING',1);


function _errorHandler($code, $message, $errFile, $errLine)
{ 

	$errorCodes = array(
	E_ERROR => 'PHP ERROR',

	E_WARNING => 'PHP WARNING',

	E_NOTICE => 'PHP NOTICE',

	E_USER_ERROR => 'USER ERROR',

	E_USER_WARNING => 'USER WARNING',

	E_USER_NOTICE => 'USER NOTICE',

	E_STRICT => 'PHP STRICT',

	E_RECOVERABLE_ERROR => 'PHP RECOVERABLE ERROR',

	E_DEPRECATED => 'PHP DEPRECATED ERROR',

	E_USER_DEPRECATED => 'USER DEPRECATED WARNING',

	E_ALL => 'PHP ALL');

  global $base_url;
  $subject = "MAJOR PROBLEM at " . $base_url . ': ' . date("F j, Y, g:i a"); 
  $body = "Error: $code\r\n"
  				."Message: $message\r\n"
  					. "errFile: $errFile\r\n" 
            . "errLine: $errLine\r\n" ;
            //. trigger_dump($GLOBALS); 
	/* 
	An email will be sent to the site administrator. 
	Its subject line will have the date and time it occurred while 
	the body will contain the state of all of the global variables.
	*/
	// define a format for the error message
	log_error(sprintf(setting('error.logFormat'),
		$errorCodes[$code],$message,$errFile,$errLine));
	
	if(EMAIL_ERRORS) email_error($subject,$body);

  return ALSO_EXECUTE_PHP_ERROR_HANDLER;
} 


function log_error($msg)
{
	return error_log($msg,PHP_SYSTEM_LOGGING);
}

function email_error($subject,$msg)
{
	return mail(NOTIFICATION_EMAIL,$subject,$msg);
}


function ttail( $data, $LogGroup = null)
{
	global $user;
	tail($data, null, null, $LogGroup );
}


function tail( $log_msg = "Msg not included...", $file = null, $function = null, $LogGroup = null )
{
	clearstatcache();
	if( isset($LogGroup) )
	{
		$LogFile = $LogGroup . '.log';
		$LogFilePath = ERROR_LOG . '/uploads/' . $LogFile;
		if( !file_exists($LogFilePath) ) return;
	}
	else
	{
		$LogFile = 'errorlog.txt';	
		if( !file_exists('bar.baz') ) return;
	}

	$trace = debug_backtrace();
	$alltrace = print_r( $trace,true);
	$function = $trace[1]['function'];
	$file = $trace[1]['file'];
	if(!isset($file)) $file = "NO FILE SPECIFIED";
	$handle = fopen( ERROR_LOG . '/uploads/'.$LogFile,'a+t');
	// $handle = fopen( ERROR_LOG . '/uploads/errorlog.txt','w');
	$log_msg = is_array($log_msg) || is_object($log_msg) ? print_r($log_msg,TRUE) : $log_msg;
	// fwrite( $handle, "\n-------------------------\n" . date('Y-m-d g:i:s a') . "\nFile: {$file}\nFunction: {$function}\n{$log_msg}\n<<AllTrace{$alltrace}<<\n\n" );
	fwrite( $handle, "\n-------------------------\nMessage: {$log_msg}\nDate: " . date('Y-m-d g:i:s a'));// . "\n<< Stack trace:\n{$alltrace}<<\n\n" );
	fclose( $handle );
}