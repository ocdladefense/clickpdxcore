<?php


namespace Clickpdx\Core\System;


class Settings
{
	private static $settings = array();
	
	public static function loadDefaults()
	{
		require_once DRUPAL_ROOT .'/sites/default/settings-default.php';
		self::$settings = $settings;
	}
	
	public static function loadSiteSettings($siteKey)
	{
	
	}

	public static function get($name)
	{
		return self::$settings[$name];
	}
}