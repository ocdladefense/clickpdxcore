<?php

use Clickpdx\Core\Database\Db_Mysql;
use Clickpdx\Core\Database\Db_MysqlAws;
use Clickpdx\Core\Database\Database;



function get_resource_info($name)
{
	global $resources;
	if(!isset($resources[$name])) throw new Exception("The resource {$name} could not be found.");
	return $resources[$name];
}


function get_resource($name)
{
	global $resources;
	if(!isset($resources[$name])) throw new Exception("Could not make a connection to the resource {$name}.");
	$func = 'get_resource_'.$resources[$name]['type'];
	return call_user_func($func,$resources[$name]);
}


function get_resource_db($c)
{
	return new $c['class']($c['username'],$c['password'],$c['hostname'],$c['database']);
}


function get_mysql_connect_params($p,$cNew=false)
{
	return array($p['hostname'],$p['username'],$p['password'],$cNew);
}

	
/**
 * Query syntax:
 *
 * db_query('SELECT * FROM catalog WHERE i=:1,array($i))
 */
function db_query($query,$params=null,$type='mysql',$debug=false)
{
	if($debug) ini_set('display_errors','1');
	$sql = Database::finalizeSql($query,$debug);
	switch($type)
	{
		case 'mysql':
			global $db_connection;
			$stmt = $db_connection->prepare($sql);
			return $stmt->execute($params);
			break;
		case 'pdo':
			$dbConn = get_connection();
			if($debug) print "Class for database is: ".get_class($dbConn);
			$stmt = $dbConn->prepare($sql);
			if($debug) print "Class for stmt is: ".get_class($stmt);
			
			foreach ($params as $named=>$value)
			{
				if($debug) print "<br />Binding {$named} to {$value}...";
				$stmt->bindValue($named,$value);
			}
			if($stmt->execute()) return $stmt;
			else throw new Exception("There was a database error.");
			break;
	}
}