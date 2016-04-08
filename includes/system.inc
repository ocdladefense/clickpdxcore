<?php
define('DRUPAL_XHTML_PAGE','xhtml');
define('DRUPAL_HTML5_PAGE','html5');
define('DRUPAL_HTML_PAGE','html');

use Clickpdx\Core\System\Settings;

$js_string = array();

function processSettings()
{	
	Settings::loadDefaults();
}	

function setting($name)
{
	return Settings::get($name);
}


function clickpdx_get_css()
{		
	global $css;
	$styles = '';
	
	foreach($css as $style)
	{
		$path = $style['path'];
		if ($style['type']=='inline')
		{
			$styles .= $style['data'];
			continue;
		}
		$styles .= "\n<link rel='stylesheet' href='{$path}' media='{$style['media']}' type='text/css' />";
	}
	return $styles;
}


function path_starts_with($startString)
{
	return strpos(drupal_get_path_alias(), $startString)===0;
}

function path_contains($string)
{
	return strpos(drupal_get_path_alias(), $string)!==false;
}


function l($url,$options=null,$domain=null,$proto=null)
{
	$abs = isset($options['absolute'])?true:false;
	$domain = $abs?$domain:'';
	
	// Set the querystring
	$querystring = '';
	return "//".$domain ."/".$url . $querystring;
}


function system_get_site_url()
{
	return $_SERVER['SERVER_NAME'];
}


function system_get_setting($settingName)
{
	global $homepage, $site_name;
	
	switch($settingName)
	{
		case 'session_cookie_name':
			return 'OCDLA_SessionId';
			break;
		case 'homepage':
			return $homepage;
			break;
		case 'site_name':
			return $site_name;
			break;
	}	
	throw new Exception("Could not locate the system setting, {$settingsName}.");
}


function system_get_homepage_callback()
{
	return system_get_setting('homepage');
}


function system_filesystem_only()
{
	global $filesystem_only;
	return $filesystem_only;
}


function system_get_resource($resourceKey)
{
	global $resources;
	return $resources[$resourceKey];
}


 
function drupal_get_active_site_dir()
{
	include(DRUPAL_ROOT .'/sites/sites.php');
	$host = $_SERVER['SERVER_NAME'];
	if(empty($sites)||!count($sites))
	{
		return DRUPAL_ROOT . "/sites/default";
	}
	foreach($sites as $site=>$dir)
	{
		if ($site==$host) return DRUPAL_ROOT ."/sites/{$dir}";
	}	
	return DRUPAL_ROOT . "/sites/default";
}


function getSettingsFile()
{
	return drupal_get_active_site_dir() . '/settings.php';
}


function get_statuses( $s = array() ) {
	global $statuses;
	return $statuses;
}


function base_path() {
	global $base_path;
	if(isset($base_path)) return $base_path;
	else return '/';
}




function clickpdx_get_path( $type = 'theme' )
{
	global $base_path;
	
	$active_theme_info = theme_get_active_theme_info();  //name=> theme_path=>
	switch($type) {
		case 'theme':
			return $base_path .$active_theme_info['theme_path'];
		break;
		case 'module':
			return $base_path . 'sites/all/modules';
			break;
		case 'library':
			return $base_path . 'sites/all/libraries';
			break;
		case 'files':
			return $base_path .drupal_get_active_site_dir() . '/files';
		default:
			return '/sites/all/themes/sockeye';
	}

}



function js_string_add($s)
{
	global $js_string;	$js_string[] = $s;
}


function get_js_string()
{
	global $js_string; return implode( "\n",$js_string);
}


function js_var($key,$value)
{
	js_string_add("Clickpdx.cache['{$key}'] = '{$value}';");
}


function drupal_get_files_dir()
{
	$dir = './' . drupal_get_active_site_dir() . '/files';
	tail( 'Files directory is: ' . $dir );
	tail( 'Realpath of files directory is: ' . realpath( $dir ) );
	if(!file_exists( realpath( $dir ) ) ) {
		throw new Exception('Files dir does not exist');
	}
	return $dir;
}




/**
 * @bug http://msdn.microsoft.com/en-us/library/cc288325%28v=vs.85%29.aspx
 *
 */
function drupal_output_handler($type = 'xhtml')
{	
	$headers = array('cachecontrol','contenttype');
	foreach( drupal_get_headers( $headers, $type ) as $header )
	{
		header( $header );
	}
}


function drupal_get_path_alias()
{
	global $path;
	return $path;
}


function drupal_get_path($path, $reset=FALSE){
	global $form_state_path;
	
	// in some instances we may want to allow a form processing to modify
	// the path/output
	// this is specified with the $reset set to TRUE to let the path be reset
	// to the value of $form_state_path
	if($reset && !empty($form_state_path)) return $form_state_path;
	else return $path;
}


/**
 * @function drupal_render_page
 * @documentation - http://api.drupal.org/api/drupal/includes%21common.inc/function/drupal_render_page/7
 *
 */
function drupal_render_page($vars)
{
	// * Make sure that $vars['node'] is set
	// * $vars['node'] has already been populated with data
	// * $node->title, $node->canonical_url, $node->type
	// * build menus here?
	if(isset($vars['primary_links']))
	{
		$vars['primary_links'] = theme('primary_links', menu_get_menu( 'primary_links' ));
	}
	
	// The page template will have access to $vars['node']
	$vars['page'] = theme('page',$vars);

	ob_start();
	print theme('html', $vars);
	$length = ob_get_length();
	header('Content-Length: ' . $length);
	ob_end_flush();
}


/**
 * @function ajax_deliver
 * @documentation - http://api.drupal.org/api/drupal/includes!ajax.inc/function/ajax_deliver/7
 *
 */
function ajax_deliver($page_callback_result, $type = 'html')
{
  if( $type == 'html' )
  {
		header("Content-Type: text/html; charset=UTF-8");
		header("Cache-Control: no-cache, must-revalidate");
		ob_start();
		print $page_callback_result;
		$length = ob_get_length();
		header('Content-Length: ' . $length);
		ob_end_flush();
	}
}

function html_deliver($page_callback_result, $type = 'html')
{
	header("Content-Type: text/html; charset=UTF-8");
	header("Cache-Control: no-cache, must-revalidate");
	ob_start();
	print $page_callback_result;
	$length = ob_get_length();
	header('Content-Length: ' . $length);
	ob_end_flush();
}


function xml_deliver($page_callback_result,$type='xml')
{
	header("Content-Type: text/xml");
	ob_start();
	print $page_callback_result;
	$length = ob_get_length();
	header('Content-Length: ' . $length);
	ob_end_flush();
}


function json_deliver($page_callback_result, $type = 'json')
{
	// $page_callback_result needs to be a PHP array
	$json = json_encode($page_callback_result);
	header("Content-Type: application/json; charset=UTF-8");
	header("Cache-Control: no-cache, must-revalidate");
	ob_start();
	print $json;
	$length = ob_get_length();
	header('Content-Length: ' . $length);
	ob_end_flush();
}

function jsonp_deliver($callback_result, $type = 'json')
{
	// $page_callback_result needs to be a PHP array
	$jscallback = $callback_result['callback'];
	$json = json_encode($callback_result['json']);
	header("Content-Type: application/json; charset=UTF-8");
	header("Cache-Control: no-cache, must-revalidate");
	ob_start();
	print $jscallback ."(" . $json . ")";
	$length = ob_get_length();
	header('Content-Length: ' . $length);
	ob_end_flush();
}


function clickpdx_goto($url,$r=301)
{
	session_write_close();
	header( "Location: $url" );
	exit;
}


function clickpdx_set_http_status($statusMsg, $statusCode)
{
	$protocol = "HTTP/1.0";
	if ("HTTP/1.1" == $_SERVER["SERVER_PROTOCOL"])
		$protocol = "HTTP/1.1";
	header("$protocol $statusCode $statusMsg", true, $statusCode);
}

function drupal_page_not_found()
{
	session_write_close();
	header('HTTP/1.0 404 Not Found');
	include('./content/error_pages/error-page-not-found.shtml');
    exit();
}

function clickpdx_access_denied()
{
	session_write_close();
	header('HTTP/1.0 403 Forbidden');
	include('./content/error_pages/error-access.html');
    exit();
}


function clickpdx_protocol_error($errorMsg)
{
	session_write_close();
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	print $errorMsg;
	exit();
}


function clickpdx_server_maintenance()
{
	clickpdx_set_http_status("Service Unavailable",503);
	header( "Retry-After: 3600" );
	include('./content/error_pages/error-maintenance.shtml');
    exit();
}


function clickpdx_set_message( $message = NULL, $type = 'status', $class = NULL )
{
	if (isset($message))
	{
		$_SESSION['messages'] = array(
			'message' => $message,
			'type' => $type,
			'class' => $class,
		);
	}
	return $_SESSION['messages'];
}