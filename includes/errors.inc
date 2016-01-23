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
	log_error($message . " in " . $errFile . " (Line $errLine)");
	
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

function _myExceptionHandler(\Exception $ex)
{
	
}

class MyExceptionHandler
{
	private static $currentFrame;
	
	private static $message = array();
	
	private static $e;
	
	private static $trace;
	
	private static function getActiveFrameInfo()
	{
		return "At line ".self::$e->getLine() .' of <code>'.self::$e->getFile().'</code>';
	}
	
	private static function getMessage()
	{
		return self::$message;
	}
	
	public static function handle(\Exception $e)
	{
		self::$e = $e;
		self::$message = $e->getMessage();
		self::$trace = $e->getTrace();
		self::__printMe();
	}
	
	public static function __toString()
	{
		$trace = array_map(array("MyExceptionHandler","getFrame"),self::$trace);
		return "<p class='trace'>".implode('<br />',$trace)."</p>";
	}
	
	public static function getFrame($frame)
	{
		static $frameNo = 0;
		$frameNo++;
		return "#{$frameNo}: In <span class='func'>{$frame['function']}</span>, line {$frame['line']} >> {$frame['file']}";
	}
	
	public static function __printMe()
	{
		print self::getStyles();
		print "<h2>Woops!  That was an error.</h2>";
		print "<p>Return to <a href='/admin'>admin</a></p>";
		print "<h3>Error Message:</h3>";
		print "<blockquote>".self::getMessage()."</blockquote>";
		print "<h3>Stack Trace:</h3><p style='border:1px solid #ccc;'>".self::getActiveFrameInfo()."</p>";
		print self::__toString();
	}
	
	private static function getStyles()
	{
		return "<style type='text/css'>.func{font-weight:bold;}.file{font-weight:bold;}.trace{background-color:#fffdd0;padding:8px;margin-left:15px;font-size:12pt; line-height:22px;}blockquote{padding-left: 16px; padding-top:15px;border-left: 5px solid #577da4; width: 700px; min-height:50px;background-color:#fffdd0; font-style: italic; line-height: 18px;}</style>";
	}
	
	public static function displayFriendly()
	{
		print "<h1>Woops!  That's an error.</h1><p>Don't worry about the details, someone has been notified.</p>";
	}
	
	public static function getHandler($env='prod')
	{
		switch($env)
		{
			case 'test':
				return array("MyExceptionhandler","handle");
				break;
			default:
				return array("MyExceptionHandler","displayFriendly");
				break;
		}
	}
	
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