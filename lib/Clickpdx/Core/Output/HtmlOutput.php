<?php
namespace Clickpdx\Core\Output;

class HtmlOutput implements Renderable
{
	private $renderer;
	
	private $container;
	
	public function setRenderer($renderer)
	{
		$this->renderer($renderer);
	}
	
	protected function getRenderEngine()
	{
		return $this->container->getRenderer();
	}
	
	public function setContainer($container)
	{
		$this->container = $container;
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
	
	public function render()
	{
		return $this->renderer->render($items);
	}
}