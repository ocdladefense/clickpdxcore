<?php

namespace Clickpdx\Core\Database;

define('DEFAULT_RESOURCE_NAME', 'default');

/**
 * Debug.
 *
 * Whether we should print any debugging information to std output.
 */
define('DATABASE_DEBUG',		false);


class Database
{

	public static function finalizeSql($sql,$debug = false, $resourceName = 'default')
	{
		$debug = $debug===true?$debug:DATABASE_DEBUG;
		// For each {table} pattern
			// Do a lookup on the pattern
				// identify the replacement, if any
		$_sql = preg_replace_callback('/{(\w*)}/mi',function($matches) use($debug,$resourceName){ if($debug) print Database::debug($matches); return Database::tableLookup($matches[1],$resourceName);},$sql);
		if($debug) print "SQL is: $_sql";
		return $_sql;
	}

	public static function debug($str)
	{
		return "<pre>".print_r($str,true)."</pre>";
	}

	public static function tableNameReplace($tableBaseName)
	{
		
	}
	
	public static function tableLookup($tableBaseName,$resourceName=null)
	{
		global $resources;
		if(!isset($resourceName)) $resourceName = DEFAULT_RESOURCE_NAME;
		$resource = system_get_resource($resourceName);
		$tableNamespaces = $resource['prefixes'];
		return isset($tableNamespaces[$tableBaseName])?$tableNamespaces[$tableBaseName]:$tableBaseName;
	}

}