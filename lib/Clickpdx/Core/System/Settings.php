<?php


namespace Clickpdx\Core\System;


class Settings
{

	private static $s = array();
	
	public static function loadDefaults()
	{
		require_once DRUPAL_ROOT .'/sites/default/settings-shared.php';
		self::$s = $settings;
		
		// This should overwrite the above $settings var.
		require_once DRUPAL_ROOT .'/sites/default/settings-default.php';
		self::$s = array_merge(self::$s,$settings);
	}
	

	public static function get($name,$default = null)
	{
		return (isset(self::$s[$name]) ? self::$s[$name] : $default);
	}
}