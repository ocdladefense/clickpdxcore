<?php
namespace Clickpdx\Core\Output;

class MenuPage extends HtmlOutput
{
	private $links; 
	
	public function __construct(array $links)
	{
		$this->links = $links;
	}
	
	public function setLinks(array $links)
	{
		$this->links = $links;
	}
	
	public function render()
	{
		return $this->linksToHtml($this->links);
	}

}