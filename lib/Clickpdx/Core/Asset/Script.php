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
		// print "Type is: ".get_class($arg) .'<br />';
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

	public function __toString()
	{
		return implode('<br />',array(
			"Path is: {$this->getAttribute('href')}",
			"Type is: {$this->type}"
		));
	}	
}