<?php
use Clickpdx\Core\Output;

namespace Clickpdx\Core\Routing;

class RouteProcessor
{
	const DEFAULT_OUTPUT_HANDLER = 'html';
	
	private static $container;
	
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
				$filepath = substr($file,0,1)=='/' ? DRUPAL_ROOT.$file : $route->getModulePath().'/'.$file;
				require($filepath);
			});
	}
	
	private static function getRouteArgumentsRecursive(Route $route,$args=array())
	{
		$check = $args;
		if(is_array($current = $route->getArgumentRules()))
		{
			$map = function($pArg,$pKey) use(&$args,$check){
				if(!count($check))
				{
					$args += array($pKey=>$pArg);
					return;
				}
				
				/**
				 * Existing key - ignore it.
				 * 
				 * If the key exists, it's because the active router
				 * has defined it with the intention of overriding it.
				 */
				if(!is_int($pKey)&&array_key_exists($pKey,$args))
				{
					return;
				}
				
				/**
				 * New key from parent (not in child)
				 * 
				 * These need to be prepended in the order they were defined.
				 * Base (parent) routes are *always prepended unless
				 * they were overridden.
				 */
				else if(!is_int($pKey))
				{
					$args = array($pKey=>$pArg) + $args;
				}
				
				/**
				 * Numeric key
				 *
				 * Since numeric keys can't be overridden,
				 * we must prepend that argument here
				 */
				else
				{
					array_unshift($args,$pArg);
				}
			};
		
			/**
			 * Prioritize arguments germain to the active route
			 *
			 * Given this hierarchy:
			 *
			 * parent/sub/child
			 * 	sub/child
			 * 		child (this route)
			 *
			 * The operation below keeps the arguments
			 * for the active router; arguments
			 * for parent routes with the same keys are considered as having
			 * been overriden by the child routes.
			 * Other keys not present from the child routes are
			 * simply appended to the argument list.
			 *
			 * It turns out that += replaces
			 * existing keys in the left operand positionally
			 * so an existing key in a child argument-array 
			 * will effectively maintain its positionality within 
			 * the array.  This has the effect of giving
			 * child argument-arrays priority in determining the "order"
			 * that arguments are passed to callbacks, which is important to
			 * easily determining this order from examining the menu router item.
			 * 
			 */
			$prepared = !count($check)?$current:array_reverse($current);
			array_walk($prepared,$map);
		}

		$parentKey = getNextMenuItemParentKey($route->getPath());
		$nextRouter = getMenuRouterItem($parentKey);
		if(!$nextRouter)
		{
			return $args;
		}
		return self::getRouteArgumentsRecursive($nextRouter,$args);
	}
	
	
		
	public static function processRouteArguments(Route $route)
	{
		$pathArgs = Route::getPathArguments($route->getPath());
		
		$rules = self::getRouteArgumentsRecursive($route);

		$p = array();
		foreach($rules as $key => $rule)
		{
			if(is_int($key)&&is_int($rule))
			{
				$p[] = $route->getPathArgument($rule);
				continue;
			}
			if(is_callable($rule))
			{
				$p[] = $rule();
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
	
	private static function doRoute($routeInstance,$callback,$argsProcessed)
	{
		return call_user_func_array(
			array($routeInstance,$callback),
			$argsProcessed
		);
	}
		 
	private static function getThemeEngine()
	{
		return self::$container->getThemeEngine();
	}
	
	public static function getThemeName($route)
	{
		return empty($route->getTheme())?
			self::getThemeEngine()->getDefaultThemeName():
				$route->getTheme();
	} 
		 
	public static function processOutputHandler($route,$vars)
	{	
		/**
		 * Invoke the callback.
		 *
		 * Invoke the callback for this Route with
		 * the specified arguments. If there are errors,
		 * capture those into $out and print them on the page.
		 */
		try
		{
			$renderArray = '';
			self::getThemeEngine()->addTemplatePath(\path_to_theme($route->getTheme()).'/templates');
			
			if(class_exists($class=$route->getRouteClass()))
			{
				/**
				 * Is the outClass renderable?
				 * If so, just render it and return that,
				 * no further processing is necessary.
				 */
				if(self::isRenderable($class))
				{
					$classOut = new $class(RouteProcessor::processRouteArguments($route));
					$classOut->setRenderer(self::getRenderer());
					$renderArray = $classOut->render();
				}
				else
				{
					$controller = new $class();	
					$controller->setContainer(self::$container);
					$callback = $route->getRouteCallback();
					$argsProcessed = RouteProcessor::processRouteArguments($route);
					$renderArray = RouteProcessor::doRoute($controller,$callback,$argsProcessed);
				}
			} 
			else
			{
				$argsProcessed = RouteProcessor::processRouteArguments($route);
				$renderArray	=	call_user_func_array(
					$route->getRouteCallback(),
					$argsProcessed
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
		// catch(RouteException $e)
		catch(Exception $e)
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
			case 'html-file':
				html_deliver($renderArray);
				break;
			case 'file':
				file_deliver($renderArray);
				break;
			case 'ajax':
				ajax_deliver($renderArray);
				break;
			case 'json':
				json_deliver($renderArray);
				break;
			case 'jsonp':
				jsonp_deliver($renderArray);
				break;
			case 'xml':
				xml_deliver($renderArray);
				break;
			default:
				if(in_array($route->getOutputHandler(), array('html','xhtml','html5')))
				{
					// Here is a reference to the renderer
					// Pass the $renderArray to a class
					// that implements Renderable
					self::$container->getOutputRenderer()->render($renderArray,$route);
				}
				else
				{
					throw new \Exception('No output handler was specified.');
				}
				break;
		}
	}
	
	public static function getRenderer()
	{
		return self::$renderer;
	}

	private static function isRenderable($class)
	{
		return in_array('Clickpdx\Core\Output\Renderable',class_implements($class));
	}
	
	public static function setContainer($container)
	{
		self::$container = $container;
	}
	
	public static function clickpdx_process_router($path)
	{
		global $statuses;
		$vars = array();
	
		/**
		 * Path
		 *
		 * We process the path that has been requested from the routing system.
		 * Attempt to match the requested path against the available routers.
		 */
		if ($path == '' || $path == '/' || $path == 'home')
		{
			$route = \clickpdx_get_homepage_router();
		}
		else
		{
			$menu_items = \drupal_get_menu_items();	
			$route = \clickpdx_get_router($path);
		}


		/**
		 * Load include files.
		 *
		 * We load the include files first.
		 * They may contain the callback functions 
		 * or access functions.
		 */
		self::loadIncludeFilesRecursive($route);



/*
		global $sess;
		print get_class($sess);
		$reflect = new \Reflection("\Ocdla\Session");
		// print $reflect->getFileName();
		if(\user_is_authenticated())
			print "<br />User was authenticated.";
		else $sess->hasAuthenticatedSession();
		exit;
		*/

		/**
		 * Access Denied
		 *
		 * If the user doesn't have access
		 * then we bail out and return a 403.
		 */
		if(!\evaluateMenuAccess($route,$path))
		{
			// print 'Menu access evaluated to false.';
			// \user_is_authenticated();
			// exit;
			\clickpdx_access_denied();
		}


		/**
		 * Page not found
		 *
		 * If there is no valid callback for the requested route
		 * then we bail out and return a 404.
		 */
		if(!$route->hasValidCallback())
		{
			\drupal_page_not_found();
			throw new \Exception('No Page Callback given for this menu item.');
		}
	
		/**
		 * Okay, everything checks out,
		 * the user has access and we've established there is a valid callback.
		 */
		self::processOutputHandler($route);
	}

}