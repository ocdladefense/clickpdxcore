<?php

namespace Clickpdx\Core\Exception;

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
				return array("Clickpdx\Core\Exception\MyExceptionhandler","handle");
				break;
			default:
				return array("Clickpdx\Core\Exception\MyExceptionHandler","displayFriendly");
				break;
		}
	}
	
}