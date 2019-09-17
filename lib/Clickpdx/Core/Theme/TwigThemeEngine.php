<?php

namespace Clickpdx\Core\Theme;

class TwigThemeEngine implements ThemeEngineInterface
{
	private $twigEnvironment;
	
	private $loader;
	
	public function __construct(\Twig_Environment $twig)
	{
		$this->twigEnvironment = $twig;
		$this->loader = new \Twig_Loader_Filesystem($this->getAllTemplatePaths(), array('cache' => TWIG_CACHE,'debug' => TWIG_DEBUG));
		$this->twigEnvironment->setLoader($this->loader);
	}
	
	
	/**
	 * 
	 */
	public function getActiveThemeInfo()
	{
		global $theme_registry;
		$active_theme = $theme_registry[\system_get_site_url()]['themes'][0];
		return $active_theme;
	}


	public function getActiveThemePath()
	{
		return theme_get_active_theme_info()['theme_path'];
	}

	
	private function getThemePath($themeName)
	{
		return 'sites/all/themes/'.$themeName;
	}

	
	public function theme($hook, &$variables = array())
	{
		$active_theme_info = \theme_get_active_theme_info();
		
		$themeName = isset($variables['theme'])?$variables['theme']:$active_theme_info['name'];
		
		$themePath = isset($variables['theme'])?$this->getThemePath($variables['theme']):$active_theme_info['theme_path'];
		
		require_once( DRUPAL_ROOT .'/'. $themePath .'/template.php' );
	
		if(!isset($active_theme_info)) 
			throw new \Exception('Invalid theme or no theme specified.');

		$is_theme_function = function_exists("theme_{$hook}") ? TRUE : FALSE;
		
		$original = $variables;	
		
		// set a variable for theme_hook_suggestions
		// $variables['theme'] = $active_theme_info['name'];

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
		$extension = '.html';
		$suggestions = array_reverse($suggestions);
		$info['function'] = null;
		$info['template_name'] = null;


			
		// define an array of possible theme functions for this hook
		$possible_funcs = array( "{$variables['theme']}_{$hook}", "theme_{$hook}" );
	
		foreach($possible_funcs as $func)
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
			$found = false;		

			foreach($suggestions as $suggestion)
			{
				$template_file = $suggestion . $extension;
				$info['template_name'] = $suggestion;
				$found = true;
				break;
			}
		
			if (!$found) throw new \Exception( "No templates found for __{$hook}__." );
			// Render the output using the template file.
			$output = $this->twigEnvironment->render($template_file, $variables);
		}

		return $output;
	}
	
	public function getAllTemplatePaths()
	{
		return array(
			DRUPAL_ROOT .'/'.\path_to_theme() .'/templates',
			DRUPAL_ROOT .'/'.\path_to_theme('default') .'/templates'
		);
	}
	
	public function addTemplatePath($templatePath)
	{
		$this->loader->prependPath(DRUPAL_ROOT .'/'.$templatePath);
	}	
	
	public function getDefaultThemePath() {}
	
	public function getDefaultTheme() {}
	
	public function getDefaultThemeName()
	{
		return 'ocdla';
	}
}