<?php

namespace Clickpdx\Core\Asset;


define( 'EXTERNAL_ASSET', 1 );

define( 'LOCAL_ASSET', 2 );

define( 'INLINE_ASSET', 3 );

class Css
{	
	const ONE_ARGUMENT = 1;
	
	private $data;
	
	private $path;
	
	private $type = LOCAL_ASSET;
	
	private $region;
	
	private $attributes = array();
	
	public static function parseFileList(array $fileList)
	{
		return array_map(function($filePath){
			return new Css($filePath);
		},$fileList);
	}
	
	public static function isFileList($data)
	{
		return count($data)>ONE_ARGUMENT;
	}
	
	public function __construct($data)
	{
		/**
		 * We guess that $data is a path
		 * to a CSS file, especially if it is a string.
		 */
		// Merge the data with the defaults,
		// overriding any with the same key.
		$this->wasPassedFilePath($data) ?
			$this->_defaults(array('href'=>$data)) :
			$this->_defaults($data);
	}
	
	private function wasPassedFilePath($constructorData)
	{
		return is_string($constructorData);
	}
	
	private function _defaults($data)
	{
		$this->attributes = $data + array('href'=>$data['path']) + array(
			'type' 			=> 'text/css',
			'media'			=> 'all',
			'rel'				=> 'stylesheet',
			'href'			=> ''
		);
		$this->type = LOCAL_ASSET;
	}
	
	private function _validate()
	{
		if (!in_array($this->type, array( LOCAL_ASSET, INLINE_ASSET, EXTERNAL_ASSET)))
			throw new \Exception('Type is a required value for the constructor.');
	}

	public function isInline()
	{
		return $this->type === INLINE_ASSET;
	}

	public function getType()
	{
		return $this->type;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function getAttributes()
	{
		return $this->attributes;
	}
	
	public function getAttribute($name)
	{
		return $this->attributes[$name];
	}

	public function __toString()
	{
		return $this->getAttribute('href').'<br />';
	}	
}