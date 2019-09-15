<?php

namespace Clickpdx\Core\Asset;


define( 'SCRIPT_EXTERNAL_FILE', 1 );

define( 'SCRIPT_LOCAL_FILE', 2 );

define( 'SCRIPT_INLINE', 3 );

class Script
{
	private $data;
	
	private $path;
	
	private $type;
	
	const ONE_ARGUMENT = 1;
	
	// defer, type="text|application/javascript"
	private $attributes;
	
	public static function isScriptObject($arg)
	{
		return is_object($arg)&&get_class($arg)==='Clickpdx\Core\Asset\Script';
	}
	
	public static function isRegion($arg)
	{
		return is_int($arg);
	}
	
	public static function parseFileList(array $fileList)
	{
		return array_map(function($filePath){
			return new Script(array(
				'type' => SCRIPT_LOCAL_FILE,
				'path' => $filePath
			));
		},$fileList);
	}
	
	public static function isFileList($data)
	{
		return is_array($data)&&count($data)>ONE_ARGUMENT;
	}
	
	public function __construct($options = array())
	{
		//print_r(func_get_args());exit;
		if (is_string($options))
		{
			$options = $this->_defaults($options);
		}
		if (!count($options)) throw new Exception('No options specified for Script.');

		$this->type = $options['type'];
		if ($this->type === SCRIPT_INLINE) $this->data = $options['data'];
		
		$this->path = $options['path'];
		$this->attributes['type'] = 'text/javascript';
		$this->attributes['src'] = $this->path;
		$this->_validate();
	}
	
	public function is_inline()
	{
		return $this->type === SCRIPT_INLINE;
	}
	
	private function _defaults($path)
	{
		return array(
			'type' => SCRIPT_LOCAL_FILE,
			'path' => $path
		);
	}
	
	private function _validate()
	{
		if ( !in_array( $this->type, array( SCRIPT_EXTERNAL_FILE, SCRIPT_LOCAL_FILE, SCRIPT_INLINE ) ) )
			throw new Exception('Type is a required value for the constructor.');
	}
	
	public function isInlineScript()
	{
		return $this->type == SCRIPT_INLINE;
	}
	
	public function setTypeAttribute($type_string)
	{
		$this->attributes['type'] = $type_string;//e.g., override for 'application/javascript'
	}
	
	public function getTypeAttribute()
	{
		return $this->attributes['type'];
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function getSrcAttribute()
	{
		return $this->attributes['src'];
	}
	
	public function getAttributes()
	{
		return $this->attributes;
	}
	
	public function getScriptType()
	{
		return $this->type;
	}

	/**
	 * clickpdx_add_js
	 *
	 * A short description of the static method.
	 */
	public static function clickpdx_add_js($jsData)
	{
		// print gettype($jsData);exit;
		global $scripts;	
		$foo = jsCapture('foo');
		$foo($jsData);

		// determine what kind of parameter the last one is
		$args 			= func_get_args();
		$lastArg 		= $args[count($args)-1];
		$firstArg		= $args[0];
	
		/**
		 * Create a default set of options that will be passed
		 * for this set of scripts.
		 */
		$options 		= is_array($lastArg) ?
			array_pop($args) :
				array();
	
		/**
		 * Set the region this script will be rendered in.
		 */
		$region = Script::isRegion($lastArg) ?
			array_pop($args) :
				THEME_SCRIPT_REGION_HEADER;
		!isset($options['region']) ?
			$options['region'] = $region :
				null;


		$jsData = count($args)>1 ?
			$args :
				$jsData;

		// print "Region is: ".$region.'<br />';	
		// print "Passed data is: ".entity_toString($jsData).'<br />';	

		// Determine if the js data is a list of css file paths.
		/*
		 * For backwards compatibility where Script might be passed directly.
		 */
		if(Script::isScriptObject($firstArg))
		{
			$assets = array($firstArg);
		}
		else
		{
			// print "Passed data is: ".entity_toString($jsData).'<br />';
			$assets = Script::isFileList($jsData) ?
				Script::parseFileList($jsData) :
					array(new Script($jsData));
		}
	
		foreach($assets as $jsAsset)
		{
			array_unshift($scripts[$region], $jsAsset);
		}
	
		return $scripts;
	}

	public function __toString()
	{
		return implode('<br />',array(
			"Path is: {$this->getAttribute('href')}",
			"Type is: {$this->type}"
		));
	}	
}