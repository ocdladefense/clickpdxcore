<?php

namespace Clickpdx\Core\Routing;

use \simpleformats;

class Route
{
	/**
	 * const PATH_SEPARATOR
	 *
	 * self::PATH_SEPARATOR
	 */
	const PATH_SEPARATOR = '/';
	
	/**
	 * var routerKey
	 *
	 * The menu key corresponding to this router item.
	 *	This may differ from the actual path if the router key
	 *	uses wildcard characters, e.g., %.
	 */
	private $routerKey;

	/**
	 * var path
	 *
	 * The actual requested path passed to this router item.
	 */
	private $path;
	
	/**
	 * var access
	 *
	 * Access can either be a boolean value or a callback function.
	 * The result of the callback determines whether the current user has access
	 * to this route.
	 */
	private $access;
	
	/**
	 * var accessArguments
	 *
	 * Arguments passed to the access callback.
	 */
	private $accessArguments;
	
	/**
	 * var title
	 *
	 * The plain text title associate with this route.
	 */
	private $title;
	
	/**
	 * var routeCallback
	 *
	 * The callback that is executed when this route is requested.
	 * We use routeCallback instead of page callback to reflect its more
	 * general use in different execution contexts.
	 */
	private $routeCallback;
	
	/**
	 * var routeClass
	 *
	 * The class that extends ControllerBase.
	 * This class should provide callback methods associated with 
	 * actual routes.
	 */
	private $routeClass;
	
	/**
	 * var routeArguments
	 *
	 * Arguments passed to the routeCallback function.
	 */
	private $routeArguments = null;
	
	/**
	 * var parameters
	 *
	 * A list of additional parameters passed to this router's
	 * callback function/method.  These might be static paramters,
	 * values from the DIC, or the results of custom functions.
	 * Parameters should be passed to the callback function/method BEFORE 
	 * the path arguments.
	 */
	private $parameters = null;
	
	/**
	 * var outputHandler
	 *
	 * The output handler is the function used to process output from this router item.
	 */
	private $outputHandler = null;
	
	/**
	 * var theme
	 *
	 * Any specific theme that should be used to format output from this route.
	 */
	private $theme;
	
	
	/**
	 * var files
	 *
	 * A list of files that should be included when this route is requested.
	 */
	private $files;
	
	/**
	 * var modulePath
	 *
	 * The path to the module that defined this route.
	 */
	private $modulePath;
	
	
	public function getPath()
	{
		return $this->path;
	}
	
	public function getKey()
	{
		return $this->routerKey;
	}

	public function __toMenuStorageItem()
	{
		return array($this->routerKey => array(
			'title' => $this->title,
			'access' => $this->access,
			'access arguments' => $this->accessArguments,
			'page callback' => $this->routeCallback,
			/*'files' => array(
				'includes/PdfController.php'
			),
			'routeClass' => 'PdfController'
			*/
			)
		);
	}

	public function __construct($routerKey,$currentPath=null,$menuItem=null)
	{
		$this->path							= $currentPath;
		$this->routerKey 				= $routerKey;
		$this->routeClass				= $menuItem['routeClass'];
		$this->access						= $menuItem['access'];
		$this->accessArguments	= $menuItem['access arguments'];
		$this->routeCallback 		= $menuItem['page callback'];
		$this->routeArguments		= $this->fetchArgumentRules($menuItem['page arguments'],$menuItem['routeArguments']);
		$this->outputHandler 		= $this->parseOutputHandler($menuItem['output_handler'],$menuItem['output handler']);
		$this->title 						= $menuItem['title'];
		$this->files 						= $this->initFiles($menuItem);
		$this->modulePath 			= $menuItem['module_path'];
		$this->parameters				= $menuItem['parameters'];
		$this->meta							= array(
										'keywords' => $menuItem['meta_description'],
										'description' => $menuItem['meta_keywords']
										);
	}
	
	private function parseOutputHandler($arg1=null,$arg2=null)
	{
		return !isset($arg2)?$arg1:$arg2;
	}
	
	public function processRouteArguments()
	{
		$pathArgs = self::getPathArguments($this->path);
		
		$rules = $this->getArgumentRules();
		// Okay, process the arguments
		// $params = array_map(
		$p = array();
		foreach($rules as $key => $rule)
		{
			if(is_int($key)&&is_int($rule))
			{
				$p[] = $this->getPathArgument($rule);
				continue;
			}
			if(is_callable($key))
			{
				$rule=is_array($rule)?$rule:array($rule);
				$p[] = call_user_func_array($key,$rule);
				continue;
			}
			else $p[] = $rule;
		}
		return $p;
	}
	
	public function getPathArgument($index)
	{
		return $this->fetchPathArguments()[$index];
	}
	
	public function getArgumentRules()
	{
		return $this->routeArguments;
	}
	
	private function fetchArgumentRules($page_arguments=null,$routeArguments=null)
	{
		if(isset($routeArguments))
		{
			return $routeArguments;
		}
		else return $page_arguments;
	}
	
	private function fetchPathArguments()
	{
		return self::getPathArguments($this->path);
	}
	
	public static function getPathArguments($path)
	{
		return explode(self::PATH_SEPARATOR,$path);
	}
	
	public function getParameters()
	{
		return $this->parameters;
	}
	
	public function getModulePath()
	{
		return $this->modulePath;
	}
	
	public function getIncludes()
	{
		if(is_null($this->files)) return array();
		return is_array($this->files)?$this->files:array($this->files);
	}
	
	private function initFiles($menuItem)
	{
		if(empty($menuItem['files'])) return null;
		return is_array($menuItem['files'])?$menuItem['files']:array($menuItem['files']);
	}
	
	public function getTheme()
	{
		return $this->theme;
	}
	
	public function hasRouteArguments()
	{
		return isset($this->routeArguments)&&count($this->routeArguments);
	}
	
	public function getAccessCallback()
	{
		return $this->access;
	}
	
	public function getAccessArguments()
	{
		return $this->accessArguments;
	}
	
	public function hasAccessArguments()
	{
		return !empty($this->accessArguments);
	}
	
	public function getRouteClass()
	{
		return $this->routeClass;
	}

	public function getTitle()
	{
		return $this->title;
	}
	
	public function getMeta($type)
	{
		return isset($this->meta[$type])?$this->meta[$type]:'';
	}
	
	public function setCallback($func)
	{
		$this->routeCallback = $func;
	}
	
	public function hasValidCallback()
	{
		return 
			is_callable($this->routeCallback)
				||
			function_exists($this->routeCallback)
				||
			$this->hasValidRouteClassCallback();
	}
	
	private function hasValidRouteClassCallback()
	{
		return class_exists($this->getRouteClass());
		return class_exists($this->getControllerClass())&&
			method_exists($this->getControllerClass(),$this->getRouteCallback());
	}
	
	public function getRouteCallback()
	{
		return $this->routeCallback;
	}
	
	public function getOutputHandler()
	{
		return empty($this->outputHandler) ? 'html' : $this->outputHandler;
	}
	
	/**
	 * Router Arguments
	 *
	 * Router arguments need to be substituted and then passed to the router's
	 * callback function.
	 */
	public function _getRouteArguments()
	{
		$routerArgs = array();
		if($route->hasRouteArguments())
		{
			print "found arguments!";
			print entity_toString($route->getRouteArguments());
			$routerArgs = array_map($route->getRouteArguments(),function($arg){
				return is_int($arg)?$path_parts[$arg]:$arg;
			});
		}
	
		print entity_toString($routerArgs);exit;
	}	
	
	public function __toString()
	{
		$arr = array(
			'Path is: ' . $this->path,
			'Route class is: ' . $this->routeClass,
			'Route callback is: ' . $this->getRouteCallback(),
			'Path arguments are: ' .\simpleFormats\simpleList($this->fetchPathArguments()),
			'Argument rules are: ' . \simpleFormats\simpleList($this->getArgumentRules()),
			'Processed arguments are: ' . \simpleFormats\simpleList($this->processRouteArguments()),
			'Theme is: ' . $this->theme,
			'Parameters are: ' .\simpleFormats\simpleList($this->getParameters()),
		);
		return "<h2>Path info for: {$this->path}</h2><p style='background-color:#ccc;padding:10px;'>".\simpleFormats\simpleList($arr,'br')."</p>";
	}
	
}