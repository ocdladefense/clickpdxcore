<?php
namespace Clickpdx\Core\Output;

class HtmlHtml extends HtmlOutput 
{
	private $templateFile;
	
	public function __construct($renderEngine,$templateFiles=array())
	{
		parent::__construct($renderEngine);
		$this->templateFile = $templateFile;
	}
	
	public function render($vars)
	{
		ob_start();
		$vars['page'] = $this->renderer->theme('page', $vars);
		$length = ob_get_length();
//		print entity_toString($vars);exit;
		print $this->renderer->theme('html', $vars);
		header('Content-Length: ' . $length);
		ob_end_flush();
	}

}