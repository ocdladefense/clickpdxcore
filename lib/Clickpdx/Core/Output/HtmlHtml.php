<?php
namespace Clickpdx\Core\Output;

class HtmlHtml extends HtmlOutput 
{
	private $templateFile;
	
	private $route;
	
	public function __construct($renderEngine,$templateFiles=array())
	{
		parent::__construct($renderEngine);
		$this->templateFile = $templateFile;
	}
	
	private function renderPage($out,&$vars)
	{
		try
		{
			$vars['page']['content']		= 'hello';//$out;
			$vars['page']['errors'] 		= $errors;
		}
		catch(\Exception $e)
		{
			if(MESSAGES_DISPLAY_ERRORS)
			{
				$vars['page']['content'] = "<div class='error'><h3>There was an error processing your request.</h3><span class='message'>".$e->getMessage()."</span></div>";
			}
		}
	}
	
	public function render($renderArray)
	{
		$args = func_get_args();
		$route = array_pop($args);
		
		$out = parent::render($renderArray);


		$vars = $this->htmlVars($route);
		
		// Pass all of the arguments for access by the template
		// Not sure if this is necessary or desirable.
		$vars['route_arguments'] 		= $route->processRouteArguments();
		

		
		// $this->renderPage($renderArray,$vars);
		$vars['page']['content'] = $out;
		
		\drupal_output_handler($route->getOutputHandler());

		$vars['page'] = $this->renderer->theme('page', $vars);
		
		ob_start();


		print $this->renderer->theme('html', $vars);
		
		$length = ob_get_length();
		
		header('Content-Length: ' . $length);
		
		
		ob_end_flush();
	}


	private function htmlVars($route)
	{
		return array(
			'statuses' 					=> $statuses,
			'title'							=> $route->getTitle(),
			'theme'							=> \Clickpdx\Core\Routing\RouteProcessor::getThemeName($route),
			'site_name'					=> \system_get_setting('site_name'),
			'meta_keywords' 		=> $route->getMeta('keywords'),
			'meta_description' 	=> $route->getMeta('description')
		);
	}
}