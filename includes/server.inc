<?php
define('MAINTENANCE_FILE','maintenance.html');


/**
 * Site maintenance.
 *
 * Determine if the website is under maintenance.  
 * There is a default method for determining if this is the case,
 * but modules can define their own methods, too.
 * If any of the modules' methods returns true then the site is considered
 * to be under maintenance.
 */
function site_maintenance()
{
	return maintenance_file_exists()||module_invoke_all('maintenance');
}


function is_server_maintenance($determineMaintenanceMethod=null)
{
	if(is_callable($determineMaintenanceMethod))
	{
		
	}
}


function clickpdx_maintenance()
{
	
}


function maintenance_file_exists($file=null)
{
	$file = isset($file)?$file:MAINTENANCE_FILE;
	return file_exists($file);
}