<?php
/**
 * @file - modules.inc
 * @description - a set of functions to load installed modules
 * for this installation
 */

function drupal_get_modules($refresh = TRUE)
{
	static $modules = array();
	if( count($modules) > 0 && !$refresh ) return $modules;
	// load configuration files for all installed modules
	// for the sake of this installation 
	// all modules with installed folders

	// below, note that ./sites/all/modules is processed last
	// consistent with drupal_load_modules	
	if ($handle = opendir( realpath(MODULE_DIR) ))
	{
	
		// loop through the directory names
		// .module files must match their respective directory names
		while (FALSE !== ($_module = readdir($handle))) {
			// make sure a configuration file exists for this module
			$conf = MODULE_DIR . '/' . $_module . '/' . $_module . '.module';
			if( is_dir( MODULE_DIR . '/' . $_module ) && file_exists( $conf ) ) $modules[] = $_module;
		}

		closedir($handle);
	}
	
	// all directory
	if( $handle = opendir( $all = realpath('./sites/all/modules') ) ) {
		while (FALSE !== ($_module = readdir($handle))) {
			// print $file . '<br />';
			// echo "$entry\n";
			// if( $module_name ==
			// make sure a configuration file exists for this module
			$conf = $all . '/' . $_module . '/' . $_module . '.module';
			if( is_dir( $all . '/' . $_module ) && file_exists( $conf ) && !in_array($_module, $modules)) {
				$modules[] = $_module;
			}
		}

		closedir($handle);	
	}
	// tail( $modules );
	// require the files, that way an error is thrown if the necessary .module is not loaded
	return $modules;
	// that's all, now the functions should be available	
}


function drupal_load_modules()
{
	$modules = drupal_get_modules($refresh = TRUE);
	$module_paths = array();
		
	// below, note that ./sites/all/modules is processed last
	// this is so that 	
	if ($handle = opendir( MODULE_DIR )) {
		// echo "Directory handle: $handle\n";
		// echo "Entries:\n";
	
		// loop through the directory names
		// .module files must match their respective directory names
		// chdir( 
		while (FALSE !== ($_module = readdir($handle))) {
			// print $file . '<br />';
			// echo "$entry\n";
			// if( $module_name ==
			// make sure a configuration file exists for this module
			$conf = MODULE_DIR . '/' . $_module . '/' . $_module . '.module';
			if( is_dir( MODULE_DIR . '/' . $_module ) && file_exists( $conf ) ) {
				$module_paths[$_module] = $conf;
			}
		}

		closedir($handle);
	}
	
	// already loaded
	$loaded = array_keys($module_paths);

	if( $handle = opendir($all = realpath('./sites/all/modules')) ) {
		
		while (FALSE !== ($_module = readdir($handle))) {
			// print $file . '<br />';
			// echo "$entry\n";
			// if( $module_name ==
			// make sure a configuration file exists for this module
			$conf = $all . '/' . $_module . '/' . $_module . '.module';
			if( is_dir( $all . '/' . $_module ) && file_exists( $conf ) && !in_array($_module, $loaded)) {
				$module_paths[$_module] = $conf;
			}
		}

		closedir($handle);	
	}
		//tail('Possible modules are: ' .print_r($module_paths, TRUE));
		$loaded = array();
	// having gathered the paths
	// load the files
	foreach($modules as $name)
	{
		$loaded[$name] = $module_paths[$name];
		require( $module_paths[$name] );
	}
}








function _drupal_load_modules($refresh=true)
{
	if(system_filesystem_only()) $modules = _get_modules_fs();
	else $modules = _get_modules();
	$loaded = array();
	// having gathered the paths
	// load the files
	foreach($modules as $name => $path)
	{
		include($path);
		drupalLoadFile(drupalGetDir($path).'/autoload.php');
		drupalLoadFile(drupalGetDir($path).'/resources.php');
		$loaded[$name] = $path;
		module_invoke($name,'init');
	}
	return $loaded;
}






function _get_modules()
{
	static $module_paths = array();
	if(system_filesystem_only())
	{
		$module_paths = _get_modules_fs();
	}
	else
	{
		$results = db_query('SELECT data FROM cms_config WHERE variable="modules"')->fetch_row();
		$module_paths = unserialize($results[0]);
	}
	return $module_paths;
}


function get_module_names()
{
	return array_keys(_get_modules());
}


function _get_modules_fs()
{
	$all = _read_modules();
	$all += _read_modules(DRUPAL_ROOT .'/sites/default/modules');
	return $all;
}



function _read_modules($dir=null,$refresh=true)
{
	static $modules = array();
	static $module_paths = array();
	if(!isset($dir))
	{
		$dir = DRUPAL_ROOT.'/sites/all/modules';
	}
	$realpath = realpath($dir);
	if ($h = opendir( $realpath )) {
		while (false !== ($file = readdir($h))) {
			$popped = explode('/',$dir);
			$_module = array_pop($popped);
			if(in_array( $file, array('.','..') )) continue;

			$_this = $dir . '/' . $file;
			if( is_dir( $_this ) ) {
				_read_modules( $_this ); 
			}
			elseif( file_exists($conf = $dir. '/' . $_module . '.module') && !in_array($_module, $modules) ) {
				$modules[] = $_module;
				$module_paths[$_module] = $conf;
			}
		}
	
		closedir($h);
		// save here
		
		return $module_paths;
	}
	else
	{

		// throw new Exception('Cannot open directory, '. $realpath .' in module.inc');
	}
}


function module_invoke($module,$hook,$args=array())
{	
	if(!function_exists($f = ("{$module}_{$hook}"))) return false;
	return call_user_func_array($f, $args);
}



function module_invoke_all() {
	$args = func_get_args();
	$hook = $args[0];
	unset($args[0]);
	$return = array();
	$modules = _get_modules();

	if(!count($modules)) return;
	foreach ($modules as $name => $_)
	{
		if(!function_exists("{$name}_{$hook}")) return false;
		$result=module_invoke($name,$hook,$args);
		if (isset($result))
		{
			if (is_array($result)) $return = array_merge_recursive($return,$result);
		  else $return[] = $result;
		}
	}

	return $return;
}