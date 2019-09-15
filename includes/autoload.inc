<?php
define('DEFAULT_AUTOLOADER_EXTENSION','php');


function createAutoloader($searchDirs,$prefix,$debug=false) {
	return function($class) use($searchDirs,$prefix,$debug)
	{
		$searchDirs = classSearchDirs($searchDirs,array('prefix'=>$prefix));
		$classFile = getClassFile($searchDirs,$class,$debug);
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
		$filePath = $dir .'/'.$class .'.'.DEFAULT_AUTOLOADER_EXTENSION;
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