<?php

namespace Clickpdx\Core\Theme;

interface ThemeEngineInterface
{
	/**
	 * 
	 */ 
	 
	public function getActiveThemeInfo();
	
	public function getActiveThemePath();
	
	public function getDefaultThemePath();
	
	public function getDefaultTheme();
	
	public function getDefaultThemeName();
	
	public function theme($hook,&$variables=array());
	
	public function addTemplatePath($templatePath);
}