<?php

namespace Clickpdx\Core\Routing;

class RouteProcessor
{
	const DEFAULT_OUTPUT_HANDLER = 'html';
	
	public static function loadIncludeFiles(Route $route)
	{
		$includes = $route->getIncludes();
		array_walk($includes,function($file) use($route){require($route->getModulePath().'/'.$file);});
	}
	
	public static function getIncludeFilesRecursive(Route $route, $files=array())
	{
		if(is_array($includes = $route->getIncludes()))
		{
			$files += $includes;
		}
		$parentKey = getNextMenuItemParentKey($route->getPath());
		$nextRouter = getMenuRouterItem($parentKey);
		if(!$nextRouter)
		{
			return $files;
		}
		return self::getIncludeFilesRecursive($nextRouter,$files);
	}
	
	public static function loadIncludeFilesRecursive(Route $route)
	{
		$includes = self::getIncludeFilesRecursive($route);
		array_walk($includes,function($file) use($route){
			require($route->getModulePath().'/'.$file);
			});
	}
	
	private static function getRouteParametersRecursive(Route $route,$params=array())
	{
		if(is_array($p = $route->getParameters()))
		{
			$params += $p;
		}
		$parentKey = getNextMenuItemParentKey($route->getPath());
		$nextRouter = getMenuRouterItem($parentKey);
		if(!$nextRouter)
		{
			return $params;
		}
		return self::getRouteParametersRecursive($nextRouter,$params);
	}
	
	private static function processRouteParameters(Route $route)
	{
		$params = self::getRouteParametersRecursive($route);
		$helpers = array();
		array_walk($params,function($func,$key) use(&$helpers){
			$helpers[$key] = $func();
			});
		return $helpers;
	}
	
	public static function getOutputHandler(Route $route)
	{
		return empty($route->getOutputHandler()) ? self::DEFAULT_OUTPUT_HANDLER : $route->getOutputHandler();
	}
	
	private static function processErrors($e,$route)
	{
		switch($route->getOutputHandler())
		{
			case 'ajax':
				\ajax_deliver($out);
				break;
			case 'json':
				\json_deliver(array('error'=>$e->getMessage()));
				break;
			case 'xml':
				\xml_deliver($out);
				break;
			default:
		}
		clickpdx_set_http_status("Internal Server Error",500);
	}
	
//	public static function argSubstitution($
	/*
	// 	$path_parts = explode('/', $path);
		$routerArgs = array_map($route->getRouteArguments(),function($arg){
			return is_int($arg)?$path_parts[$arg]:$arg;
		});
		 foreach( as $arg)
		 {
		 	// page arguments is a special array
		 	// whose members have various meanings
		 	// if an array member is an integer, then it specifies a part of the path
		 	// otherwise if it is a string, object or array, then we pass that literal or variable along as is
		 	// for example, we can pass $user to a page callback
		 	if() $routerArgs[] = ;
		 	else $routerArgs[] = $arg;
		 }
		 */
	public static function processOutputHandler($route,$vars)
	{	
		/**
		 * Invoke the callback.
		 *
		 * Invoke the callback for this Route with
		 * the specified arguments. If there are errors,
		 * capture those into $out and print them on the page.
		 */
		 // print entity_toString(RouteProcessor::processRouteParameters($route));exit;
		try
		{
			if(class_exists($class=$route->getRouteClass()))
			{
				$controller = new $class();
				$controller->setContainer(new \Clickpdx\Core\DependencyInjection\DependencyInjectionContainer());
				$out = call_user_func_array(
					array($controller,$route->getRouteCallback()),
					array_merge(
						array_reverse(RouteProcessor::processRouteParameters($route)),
						$route->getRouteArguments()
					)
				);
			} 
			else
			{

				$out	=	call_user_func_array(
					$route->getRouteCallback(),
					array_merge(
						array_reverse(RouteProcessor::processRouteParameters($route)),
						$route->getRouteArguments()
					)
				);
			}
		}
		/**
		 * Process fatal errors
		 *
		 * By "fatal" we simply mean that something
		 * has happened whereby the route should return only an 
		 * error code.  Otherwise, other exceptions and errors
		 * should be processed, logged and useful output could still be returned
		 * to the client.
		 */
		catch(RouteException $e)
		{
			self::processErrors($e,$route);
			exit;
		}
		/**
		 * Other exceptions
		 *
		 * We deliberately do not catch other exceptions.
		 * Instead we let those fall through.
		 * Alternatively, we could catch them and display them in a red box.
		 */
		/*
		catch(\Exception $e)
		{
			$errors = $e->getMessage();
		}
		*/
		
		
		switch($route->getOutputHandler())
		{
			case 'ajax':
				ajax_deliver($out);
				break;
			case 'json':
				json_deliver($out);
				break;
			case 'xml':
				xml_deliver($out);
				break;
			default:
				if(in_array($route->getOutputHandler(), array('html','xhtml','html5')))
				{
					try
					{
						$vars['page']['content']		= $out;
						$vars['page']['errors'] 		= $errors;
						$vars['route_arguments'] 		= $route->getRouteArguments();
					}
					catch(Exception $e)
					{
						if(MESSAGES_DISPLAY_ERRORS)
						{
							$vars['page']['content'] = "<div class='error'><h3>There was an error processing your request.</h3><span class='message'>".$e->getMessage()."</span></div>";
						}
					}
					\drupal_output_handler($route->getOutputHandler());
					// render the $regions, $menus, $header and $footer
					\drupal_render_page($vars);
				}
				else throw new \Exception('No output handler was specified.');
				break;
		}
	}

}