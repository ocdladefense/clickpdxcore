<?php
namespace Clickpdx\Core\Output;

class HtmlPage extends HtmlOutput 
{
	private $templateFile;
	
	public function __construct($templateFile)
	{
		$this->templateFile = $templateFile;
	}
	
	public function render()
	{
		$file = DRUPAL_ROOT .'/'.$this->templateFile;
		return file_get_contents($file);
		return file_get_contents($this->templateFile);
	}

}