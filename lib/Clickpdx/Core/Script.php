<?php

namespace Clickpdx\Core;


define( 'SCRIPT_EXTERNAL_FILE', 1 );
define( 'SCRIPT_LOCAL_FILE', 2 );
define( 'SCRIPT_INLINE', 3 );

class Script {
	private $data;
	private $path;
	private $type;
	private $region;
	
	// defer, type="text|application/javascript"
	private $attributes;
	
	public function __construct( $options = array() ) {
		if ( !count( $options ) ) throw new Exception('No options specified for Script.');

		$this->type = $options['type'];
		if( $this->type === SCRIPT_INLINE ) $this->data = $options['data'];
		
		$this->path = $options['path'];
		$this->attributes['type'] = 'text/javascript';
		$this->attributes['src'] = $this->path;
		$this->_validate();
	}
	
	public function is_inline() {
		if( $this->type === SCRIPT_INLINE ) return TRUE;
		else return FALSE;
	}
	
	private function _validate() {
		if ( !in_array( $this->type, array( SCRIPT_EXTERNAL_FILE, SCRIPT_LOCAL_FILE, SCRIPT_INLINE ) ) )
			throw new Exception('Type is a required value for the constructor.');
	}
	
	public function isInlineScript() {
		if( $this->type == SCRIPT_INLINE ) return TRUE;
		else return FALSE;
	}
	
	public function setTypeAttribute( $type_string ) {
		$this->attributes['type'] = $type_string;//e.g., override for 'application/javascript'
	}
	
	public function getTypeAttribute() {
		return $this->attributes['type'];
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function getSrcAttribute() {
		return $this->attributes['src'];
	}
	
	public function getAttributes() {
		return $this->attributes;
	}
	
	public function getScriptType() {
		return $this->type;
	}
	
}