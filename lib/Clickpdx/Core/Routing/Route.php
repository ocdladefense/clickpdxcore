<?php

namespace Clickpdx\Core\Routing;


class Route
{
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

	public function __construct($routerKey,$currentPath=null,$menuItem=null)
	{
		$this->path							= $currentPath;
		$this->routerKey 				= $routerKey;
		$this->routeClass				= $menuItem['routeClass'];
		$this->access						= $menuItem['access'];
		$this->accessArguments	= $menuItem['access arguments'];
		$this->routeCallback 		= $menuItem['page callback'];
		$this->routeArguments		= $this->processPathArguments($menuItem['page arguments']);
		$this->outputHandler 		= $menuItem['output_handler'];
		$this->title 						= $menuItem['title'];
		$this->files 						= $this->initFiles($menuItem);
		$this->modulePath 			= $menuItem['module_path'];
		$this->parameters				= $menuItem['parameters'];
		$this->meta							= array(
										'keywords' => $menuItem['meta_description'],
										'description' => $menuItem['meta_keywords']
										);
	}
	
	private function processPathArguments($page_arguments=null)
	{
		if(!isset($page_arguments))
		{
			return self::getPathArguments($this->path);
		}
	}
	
	public function getParameters()
	{
		return $this->parameters;
	}
	
	public static function getPathArguments($path)
	{
		return explode('/',$path);
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
	
	private function getControllerClass()
	{
		return $this->routeClass;
	}
	public function getRouteArguments()
	{
		return $this->routeArguments;
	}

	public function getTitle()
	{
		return $this->title;
	}
	
	public function getMeta($type)
	{
		return isset($this->meta[$type])?$this->meta[$type]:'';
	}
	
	public function hasValidCallback()
	{
		return function_exists($this->routeCallback)||$this->hasValidControllerClassCallback();
	}
	private function hasValidControllerClassCallback()
	{
		return class_exists($this->getControllerClass())&&
			method_exists($this->getControllerClass(),$this->getRouteCallback());
	}
	public function getRouteCallback()
	{
		return $this->routeCallback;
	}
	
	public function getRouteClass()
	{
		return $this->routeClass;
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
	
}