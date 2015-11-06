<?php
define( 'THEME_SCRIPT_REGION_HEADER', 1 );
define( 'THEME_SCRIPT_REGION_FOOTER', 2 );
$scripts = array();
$scripts[THEME_SCRIPT_REGION_HEADER] = array();
$scripts[THEME_SCRIPT_REGION_FOOTER] = array();

$css = array();
$templates = array();


function add_html_comment($comment,$args=array())
{
	return "<!-- {$comment} -->";
}


function drupal_get_headers( $list = array('contenttype'), $type = NULL) {
	global $xml;
	$headers = array();
	$mime_types = array(
		'xml'=>'application/xml',
		'xhtml'=>'application/xhtml+xml',
		'html'=>'text/html'
	);
	tail('$type is: ' . $type );
	
	switch($type)
	{
		case DRUPAL_HTML5_PAGE:
			$allowed_types = array('xml'=>NULL,'xhtml'=>NULL,'html'=>'text/html');//for HTML5 markup
			break;
		case DRUPAL_XHTML_PAGE:
			$allowed_types = array('xml'=>'application/xml','xhtml'=>'application/xhtml+xml','html'=>'text/html');//for XHTML markup
			break;
		case DRUPAL_HTML_PAGE:
			$allowed_types = array('xml'=>NULL,'xhtml'=>NULL,'html'=>'text/html');//for HTML markup
			break;	
	}

	// find out which mime types are allowed by the server
	$accepted_types = $_SERVER['HTTP_ACCEPT'];
	tail( 'HTTP_ACCEPT types are: ' . $accepted_types );
	tail( 'Allowed types are: ' . print_r($allowed_types, TRUE));
	
	$mime_types = array_merge($mime_types, $allowed_types);
	if (stristr($_SERVER["HTTP_ACCEPT"], $mime_types['xhtml'])) {
		$mime_type = $mime_types['xhtml'];
		$xml = TRUE;
	}
	elseif (stristr($_SERVER["HTTP_ACCEPT"], $mime_types['xml'])) {
		$mime_type = $mime_types['xml'];
		$xml = TRUE;
	}
	elseif (stristr($_SERVER["HTTP_ACCEPT"], $mime_types['html'])) {
		$mime_type = $mime_types['html'];
		$xml = FALSE;
	}
	else {
		$mime_type = $mime_types['html'];
		$xml = FALSE;
	}
	$headers['cachecontrol'] = array(
		'variables' => array('no-cache','must-revalidate'),
		'value' => array("Cache-Control: no-cache, must-revalidate"),
	);
	$headers['contenttype'] = array(
		'variables' => array($mime_type),
		'value' => array("Content-Type: {$mime_type}; charset=utf-8"),
	);
	
	$header_headers = array();
	foreach( $list AS $key )
	{
		if(isset($headers[$key]))
		{
			$header_headers[] = $headers[$key]['value'][0];
		}
	}
	
	tail($header_headers);
	return $header_headers;
}



function theme_get_active_theme_info()
{
	global $theme_registry;
	$active_theme = $theme_registry[system_get_site_url()]['themes'][0];
	return $active_theme;
}


		
function drupal_add_js( Clickpdx\Core\Script $script, $region = SCRIPT_REGION_HEADER )
{
	// add on to the global $scripts array
	global $scripts;	
	array_unshift( $scripts[$region], $script );
}


function theme_javascripts(/*Script array*/ $scripts)
{
	$out = array();
	foreach( $scripts AS $Script ) {
		$out[] = $Script->is_inline() ? theme_javascript_inline( $Script ) : theme_javascript_file( $Script );
	}
	$html = "\n\n" . implode( "\n", $out ) . "\n\n";
	return $html;
}


function theme_javascript_file(Clickpdx\Core\Script  $Script)
{
	$attributes = $Script->getAttributes();
	$html = "<script src='{$Script->getSrcAttribute()}' type='{$Script->getTypeAttribute()}'></script>";
	return $html;
}


function theme_javascript_inline( Clickpdx\Core\Script  $Script )
{
	if( $Script->getType() !== SCRIPT_INLINE ) throw new Exception( 'Cannot theme a script for inline if it isn\'t inline.' );
	$inline = array();
	$inline['prefix'] = "<script type='{$Script->getTypeAttribute()}'>\n";
	$inline['prefix'] .= '<!--//--><![CDATA[//><!--'."\n";
	//$inline['data'] = 'jQuery.extend(Drupal.settings, { "basePath": "/", "colorbox": { "transition": "elastic", "speed": 350, "opacity": "0.85", "slideshow": false, "slideshowAuto": false, "slideshowSpeed": 2500, "slideshowStart": "", "slideshowStop": "", "current": "{current} of {total}", "previous": "« Prev", "next": "Next »", "close": "Close", "overlayClose": true, "maxWidth": "100%", "maxHeight": "100%" }, "nice_menus_options": { "delay": "800", "speed": "slow" }, "shadowbox": { "animate": 1, "animateFade": 1, "animSequence": "wh", "auto_enable_all_images": 0, "auto_gallery": 0, "autoplayMovies": true, "continuous": 0, "counterLimit": "10", "counterType": "default", "displayCounter": 1, "displayNav": 1, "enableKeys": 1, "fadeDuration": "0.35", "handleOversize": "resize", "handleUnsupported": "link", "initialHeight": 160, "initialWidth": 320, "language": "en", "modal": false, "overlayColor": "#333", "overlayOpacity": "0.6", "players": null, "resizeDuration": "0.35", "showMovieControls": 1, "slideshowDelay": "0", "troubleElements": [ "select", "embed", "canvas" ], "viewportPadding": 20, "useSizzle": 0 } });'."\n";
	$inline['suffix'] = "\n".'//--><!]]>'."\n";
	$inline['suffix'] .= '</script>';
	return $inline['prefix'] . $Script->getData() . $inline['suffix'];
}


function page_download() {
	//$filename = substr($catalog["DownloadLocation"],strrpos($catalog["DownloadLocation"],'\\')+1);
	$filename = 'DUIITrialNotebook_2008.pdf';
	$drive = 'F';
	// scp sysadmin@10.0.100.100:/library/webserver/uploads/
	$filesize = filesize($drive .':\inetpub\ocdla\uploads\\'.$filename);
	
	// We'll be outputting a ZIP
	header('Content-Disposition: attachment; filename='.basename($drive .":\\inetpub\\ocdla\\uploads\\$filename"));
	header('Content-Type: application/octet-stream');
	//header("Content-Type: application/force-download");
	header("Content-Type: application/download");
	header("Content-Description: File Transfer");  
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . $filesize);
	ob_clean();
	flush();
	
	
	//Change to the appropriate directory to serve up the file.
	chdir('\inetpub\ocdla\uploads');
	$local_file = $filename;
	
	//Output the file contents to the browser.
	if(file_exists($local_file) && is_file($local_file)) { readfile($local_file); }
	else {
			die('Error: The file '.$filename.' does not exist!');
	}

}





function theme_footer_info( $info = NULL )
{
	$prefix = '<ul id="site-info"><li>';
	$suffix = '</li></ul>';
	$output = $prefix . implode('</li><li>',$info) . $suffix;
	return $output;
}




function drupal_add_css($css_array)
{
	global $css;
	array_push($css, $css_array);
}


function theme_render_template( $template, &$vars )
{
	$file = DRUPAL_ROOT . '/' . $template;
	extract($vars, EXTR_SKIP); // Extract the variables to a local namespace
	ob_start();
	require $file; // Include the template file
	return ob_get_clean(); // End buffering and return its contents
}


function phptemplate_preprocess_html(&$vars)
{
	global $css;
	
	$vars['theme_hook_suggestions'] = array('html--5','html',);
	$vars['classes'] = array();	

	$vars['head_title'] = SITE_NAME . ' - ' . $vars['title'];
 
	$vars['styles'] = "";
	$css_files = array_reverse($css);
	foreach( $css_files AS $style ) {
		$path = strpos($style['path'],'http')!==FALSE ? $style['path'] : '/' . $style['path'];
		if( $style['type']=='inline' ) { $vars['styles'] .= $style['data']; continue; }
		$vars['styles'] .= "\n<link rel=\"stylesheet\" href=\"{$path}\" media=\"{$style['media']}\" type=\"text/css\" />";
	}
}


function phptemplate_preprocess_page( &$vars ) {
	global $templates;

	 //some other stuff
	//if (isset($vars['node'])) {  
//		$vars['theme_hook_suggestions'][] = 'page'. $variables['node']->type;
	//	$vars['theme_hook_suggestions'][] = "page__node__" . $variables['node']->nid;
	//}
	$vars['theme_hook_suggestion'] = 'page';
	/**
	 * @file
	 *
	 */

	
	$menu = array();
	$menu_names = drupal_get_menu_items();
	foreach( $menu_names AS $item ) {
		$menu[] = $item['name'];
	}
	
	$vars['main_menu'] = '<ul class="menu"><li class="menu-item">' . implode('</li><li class="menu-item">', $menu ) . '</li></ul>';
	
	
	$toolbar = array( 'lod','cart','account');
	$vars['toolbar'] = '<ul class="menu"><li class="menu-item">' . implode('</li><li class="menu-item">', $toolbar ) . '</li></ul>';	
}


function theme($hook, $variables = array()) {
	// define paths for themes registered for specific domains
	// normally this would be done through separate databases
	// but instead we need to load this data through .info files
	if(FALSE)/*@jbernal*/ load_theme( $_based_on_domain );
	$active_theme_info = theme_get_active_theme_info();  //name=> theme_path=>
	require_once( DRUPAL_ROOT .'/'. $active_theme_info['theme_path'] .'/template.php' );
	
	if(!isset($active_theme_info)) throw new Exception('Invalid theme or no theme specified.');

	$is_theme_function = function_exists("theme_{$hook}") ? TRUE : FALSE;
	$original = $variables;	
	// set a variable for theme_hook_suggestions
	$variables['theme'] = $active_theme_info['name'];

	$variables['theme_hook_suggestions'] = array($hook);// e.g. set a default template implementation, 'node.tpl.php', 'page.tpl.php', 'html.tpl.php'
	
	$variables['theme_hook_suggestion'] = NULL;
	
	// study /includes/theme.inc lines 850-888
	// loop through the process and preprocess functions for this hook
	// for now only loop through the preprocess functions
	// preprocess functions should alter and add to variables
	
	// predefine some $func here
	$theme_engine = 'phptemplate';
	$preprocess_funcs = array();
	$preprocess_funcs[] = $theme_engine . '_preprocess_' . $hook;
	$preprocess_funcs[] = $variables['theme'] . '_preprocess_' . $hook;
	
	foreach(get_module_names() as $module)
	{
		if(function_exists($func = ($module . '_preprocess_'.$hook)))
		$preprocess_funcs[] = $func;
	}
	// print entity_toString($preprocess_funcs);exit;
	
	foreach($preprocess_funcs AS $func)
	{
		if(function_exists($func)) {
			$func($variables);
		}
	}
	
	
	$info = array();
	
	// now we should have several altered variables
	// as well as theme_hook_suggestions
	// /includes/theme.inc lines 875
	
	// Generate the output using either a function or a template.
	// If the preprocess/process functions specified hook suggestions, and the
	// suggestion exists in the theme registry, use it instead of the hook that
	// theme() was called with. This allows the preprocess/process step to
	// route to a more specific theme hook. For example, a function may call
	// theme('node', ...), but a preprocess function can add 'node__article' as
	// a suggestion, enabling a theme to have an alternate template file for
	// article nodes. Suggestions are checked in the following order:
	// - The 'theme_hook_suggestion' variable is checked first. It overrides
	//   all others.
	// - The 'theme_hook_suggestions' variable is checked in FILO order, so the
	//   last suggestion added to the array takes precedence over suggestions
	//   added earlier.
	$suggestions = array();
	if (!empty($variables['theme_hook_suggestions']))
	{
		$suggestions = $variables['theme_hook_suggestions'];
	}
	if (!empty($variables['theme_hook_suggestion']))
	{
		$suggestions[] = $variables['theme_hook_suggestion']; // makes this key take priority once we reverse it, below
	}
												

	$output = '';
	// Default render function and extension.
	$render_function = 'theme_render_template';
	$extension = '.tpl.php';
	$suggestions = array_reverse($suggestions);
	$info['function'] = NULL;
	$info['template_name'] = NULL;


			
	// define an array of possible theme functions for this hook
	$possible_funcs = array( "{$variables['theme']}_{$hook}", "theme_{$hook}" );
	
	foreach($possible_funcs AS $func)
	{
		if(function_exists($func))
		{
			$info['function'] = $func;
			$output = $info['function']($original);
			break;    			
		}
	}

	if (!isset($info['function']))
	{	 
		$found = FALSE;		

		foreach($suggestions as $suggestion)
		{
			$template_file = $active_theme_info['theme_path'] . '/templates/' . $suggestion . $extension;
			$info['template_name'] = $suggestion;
			$found = TRUE;
			break;
		}
		
		if (!$found) throw new Exception( "No templates found for __{$hook}__." );
		// Render the output using the template file.
		
		// we should have looped through theme_hook_suggestions to make sure one exists
		// tail( 'Info for this themeable item is: '. print_r( $info, TRUE ));

		$output = $render_function($template_file, $variables);
	}

	return $output;
}


function theme_primary_links( &$vars ) {
	 $output = "<div id='primary_links'>".implode('',$menu)."</div>";
	 return $output;
}