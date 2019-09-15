<?php

use Clickpdx\Core\Database\Db_Mysql;
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
	
	if(!isset($resources[$name])) {
		throw new Exception("Resource, \"{$name}\" is not defined.  Check your settings files.");
	}
	
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
 *
 * When using the 'PDO' driver through doctrine:
 * ----------------------------------------
 * db_query("SELECT * FROM {downloads} WHERE file_creation_time 
 *		IS NULL AND entry_time >= :past
 *		AND memberid != 0",array('past'=>$past),'pdo')->fetchAll();
 * 
 * See: http://docs.doctrine-project.org/projects/
 doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html
 * for more information.
 *
 * When using the 'MySQL' driver through the core library:
 * ----------------------------------------
 * 	$stmt = db_query('SELECT * FROM {members} m LEFT JOIN {downloads} d 
 *		ON(m.id=d.memberid) JOIN {catalog} cat ON(cat.i=d.productid) WHERE 
 *		memberid=:1 AND productid=:2',array($memberid,$productid));
 *	$this->info = $stmt->fetch_assoc();
 */
function db_query($query,$params=null,$type='mysql',$debug=false)
{
		global $db_connection;
		
		if($debug) ini_set('display_errors','1');
		
		$sql = Database::finalizeSql($query,$debug);
		  
		switch($type)
		{
			case 'mysql':

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


/**
 * Query syntax:
 *
 * db_query_pdo('SELECT * FROM catalog WHERE i=:1,array($i))
 *
 * When using the 'PDO' driver through doctrine:
 * ----------------------------------------
 * db_query("SELECT * FROM {downloads} WHERE file_creation_time 
 *		IS NULL AND entry_time >= :past
 *		AND memberid != 0",array('past'=>$past),'pdo')->fetchAll();
 * 
 * See: http://docs.doctrine-project.org/projects/
 doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html
 * for more information.
 *
 * When using the 'MySQL' driver through the core library:
 * ----------------------------------------
 * 	$stmt = db_query('SELECT * FROM {members} m LEFT JOIN {downloads} d 
 *		ON(m.id=d.memberid) JOIN {catalog} cat ON(cat.i=d.productid) WHERE 
 *		memberid=:1 AND productid=:2',array($memberid,$productid));
 *	$this->info = $stmt->fetch_assoc();
 */
function db_query_pdo($resource,$query,$params=null,$debug=false)
{
	if($debug) ini_set('display_errors','1');
	$sql = Database::finalizeSql($query,$debug,$resource);
	$dbConn = get_connection($resource);
	if($debug) print "Class for database is: ".get_class($dbConn);
	$stmt = $dbConn->prepare($sql);
	if($debug) print "Class for stmt is: ".get_class($stmt);

	foreach ($params as $named=>$value)
	{
		if($debug) print "<br />Binding {$named} to {$value}...";
		$stmt->bindValue($named,$value);
	}
	if($stmt->execute()) return $stmt;
}