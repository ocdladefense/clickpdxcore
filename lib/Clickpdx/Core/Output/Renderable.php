<?php
namespace Clickpdx\Core\Output;

interface Renderable
{
	public function render($vars);
	
	public function processContent();
	
	function processPage();
	
	 function processBlocks();
	
	 function processHtml();
	
	 function processNode();
	
	 public function processAttachedJs($jsArray);
	
	 function processAttachedCss($cssArray);
	
	 function processAttached();
}