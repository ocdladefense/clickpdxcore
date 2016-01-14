<?php
namespace Clickpdx\Core\Output;

class HtmlOutput implements Renderable
{
	protected $renderer;
	
	public function setRenderer($renderer)
	{
		$this->renderer = $renderer;
	}
	
	public function __construct($renderer=null)
	{
		$this->renderer = $renderer;
	}
	
	protected function getRenderEngine()
	{
		return $this->renderer;
	}
	
	private function formatDummyLink($title,$url)
	{
		$href=is_numeric($url)?"#":$url;
		return "<p><a href='{$href}'>$title</a></p>";
	}
	
	private function formatLink($link,$key)
	{
		if(is_array($link)&&$link['url'])
		{
			$path = $link['abs']?$link['url']:(\base_path().$link['url']);
			$target = $link['target']?"target={$link['target']}":'';
			return "<div class='admin-link'>
				<a href='{$path}' $target>{$link['title']}</a>
				<p class='admin-description'>{$link['desc']}</p>
			</div>";
		}
		else
		{
			return $this->formatDummyLink($link,$key);
		}
	}
	
	protected function linksToHtml(array $links)
	{
		$map = array_map(array($this,"formatLink"),$links,array_keys($links));
		return implode("\n",$map);
	}
	
	public function render($vars)
	{
		return $this->renderer->render($items);
	}
}