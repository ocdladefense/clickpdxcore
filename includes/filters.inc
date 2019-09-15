<?php
function filter_wrapped_tags( $str ) {
	$str = trim( $str );
	// only process this if the first
	if( preg_match('/^\<p\>/i', $str) !== 1 ) return $str;
	$patterns = array('/^\<p\>/i', '/\<\/p\>$/i');
	$str = preg_replace($patterns, '', $str);
	if(is_null($str)) throw new Error('There was an error filtering a text field. Errono: ' .preg_last_error());
	return $str;
}

function filter_invalid_tags( $str ) {
	$str = trim( $str );
	// only process this if the first
	$patterns = array('/\<\s?\/?body\>/i', '/\<\s?\/?html\>/i');  
	$str = preg_replace($patterns, '', $str);
	if(is_null($str)) throw new Error('There was an error filtering a text field. Errono: ' .preg_last_error());
	return $str;
}

function filter_html_corrector( $str ) {
	$str = trim( $str );
	// only process this if the first
	$patterns = array('/\<\/?br\s?\/?>/mi', '/\s\&\s/mi');
	$replacements = array('<br \/>',' &amp; ');
	$str = preg_replace($patterns, $replacements, $str);
	if(is_null($str)) throw new Error('There was an error filtering a text field. Errono: ' .preg_last_error());
	return $str;
}