<?php
$autoloaderExt = 'php';


/**
 * Core includes
 * 
 * Core includes are required before any additional 
 * processing of the router can be done.
 */
$theFiles = array("errors.inc","database.inc","session.inc","string.inc","file.inc","download.inc","system.inc","module.inc","menu.inc","form.inc","utilities.inc","theme.inc","server.inc","filters.inc","node.inc","user.inc","html.inc","sso.inc","cookie.inc","curl.inc");




foreach($theFiles as $file){
	require("includes/".$file);
}


/**
 * Core autoloader.
 *
 * Responsible for loading most core classes.
 */
$core = createAutoloader(array('lib'),__DIR__,false);



spl_autoload_register($core,true,false);











function createAutoloader($searchDirs,$prefix,$debug=false) {
	return function($class) use($searchDirs,$prefix,$debug)
	{
		$searchDirs = classSearchDirs($searchDirs,array('prefix'=>$prefix));
		$classFile = getClassFile($searchDirs,$class,$debug);
		if($debug) {
			print "Classfile is: {$classFile}";
		}
		if(false!==$classFile)
		{
			loadClassFile($classFile);
		}
	};
}


function classSearchDirs(Array $paths,$options)
{
	if(isset($options['prefix']))
	{
		array_walk($paths,'pathPrepend',$options['prefix']);		
	}
	
	return $paths;
}


function pathPrepend(&$str,$key,$prefix)
{
	$str = $prefix .'/'.$str;
}


function namespaceToPath($autoloadClassCandidate)
{
	return str_replace('\\','/',$autoloadClassCandidate);
}


function getClassFile($searchDirs,$class,$debug=false)
{
	$class = namespaceToPath($class);
	
	foreach($searchDirs as $dir)
	{
		$filePath = $dir .'/'.$class .'.php';
		if($debug) print "\n<br />Searching {$filePath} for {$class}.";
		if(file_exists($filePath))
		{
			if($debug) print "\n<br />{$class} was successfully located at {$filePath}!";
			return $filePath;
			break;
		}
	}
	
	if($debug) print "\n<br />{$class} not found!";
	
	return false;
}


function loadClassFile($filePath)
{
	include_once($filePath);
}