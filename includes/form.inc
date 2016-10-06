<?php
function drupal_get_forms()
{
	$forms = array('ocdla_add_to_cart_form');
	return $forms;
}


function form_render_products($items, $qtys)
{
	// test for the number of items
	$output = NULL;
	$num_items = count($items);
	foreach($items AS $model)
	{
		$qty = array_shift( $qtys );
		$output .= "<input name='itemid[]' type='hidden' value='{$model}' />";
		$output .= "<input name='quantity[]' type='hidden' value='{$qty}' />";
	}
	return $output;	
}


function drupal_process_form($form, &$form_state)
{
	global $form_state_path, $statuses, $request, $user;
	$test = $user->uid == 25060 ? true : false;
	$test = false;
	
	if($test) print "Processing add to cart form...";
	
	$form_id = !empty($form_state['values']['form_id']) ? 
		$form_state['values']['form_id'] : 
		$form['#form_id'];
	
	$validated = FALSE;
	if($test) print "Form id is: {$form_id}.";

	
	if(function_exists( $vFunc = ($form_id . '_validate') ))
	{
		try
		{
			$validated = $vFunc( $form, $form_state );
			if($test) print_r($form_state);
		}
		catch(Exception $e)
		{
			print $e->getMessage(); exit;
		}
	}
	else throw new Exception("Validation function $validate_func not found.");
	
	$submit_funcs = array( $form_id . '_submit' );
	if(isset($form['#submit']))
	{
		foreach($form['#submit'] as $func)
		{
			$submit_funcs[] = $func;
		}
	}
		
	$statuses = $form_state['messages'];
	
	if(!$validated)
	{
		if(isset($form_state['redirect'])&&count($form_state['redirect'])>1)
		{
		 drupal_process_form_redirect($form_state);
		}
		else print "Unable to process your request.";
		exit;
	}
	else
	{
		drupal_process_submit_funcs($submit_funcs, $form, $form_state);
	}
	
	
	if(isset($form_state['#api'])&&$form_state['#api']===true)
	{
		return $form;
	}
	elseif(isset($form_state['redirect']))
	{
		drupal_process_form_redirect($form_state);
	}
	return $form;
}


function drupal_process_form_redirect(&$form_state)
{
	global $form_state_path, $statuses, $request, $user;
	// print "Cart action is: {$form_state['action']}.";
	$statuses = $form_state['messages'];
	$server = isset($form_state['redirect']['server']) ? 
		$form_state['redirect']['server'] : 
		CART_DEFAULT_REDIRECT_SERVER;
		
	$protocol = isset($form_state['redirect']['protocol']) ? 
		$form_state['redirect']['protocol'] : 
			'https://';
			
	$dest = urlencode($form_state['redirect']['ref']);
	// print "Destination will be {$dest}.";exit;
	$goto = $request->createFullUri( $form_state['redirect']['value'] );
	
	// Redirect the user
	// 	+ There are two kinds of redirects
	//	+ either internal or external.
	if(!empty($server))
	{
		clickpdx_goto("{$protocol}{$server}/{$form_state['redirect']['value']}?retURL=$dest");
	}
	else
	{
		clickpdx_goto($goto);
	}
}



function drupal_process_submit_funcs($submit_funcs, $form, &$form_state)
{
	foreach($submit_funcs as $submit_func)
	{
		if(function_exists( $submit_func ))
		{
			$submit_func( $form, $form_state );
		}
	}
}


function clickpdx_get_form($form_id)
{
	global $request, $user;
	$form_state = array();
	$args = func_get_args();
	array_shift($args);
	
	$form_state['redirect'] = array();
	$form_state['build_info']['args'] = $args;
	$form_state['method'] = $request->getRequestMethod();
	$form_state['input'] = $request->getRequestValues();
	
	// Normally, this is where we would validate the 
	//	+ input values against the actual form structure.
	if(isset($form_state['input']))
	{
		foreach($form_state['input'] as $key => $value)
		{
			$form_state['values'][$key] = $value;
		}
	}

	$constructor = $form_id;

	if(function_exists($constructor))
	{
		$form = $constructor($form,$form_state);
		$form['#form_id'] = $form['form_id']['#value'];
	}
	else
	{
		throw new Exception("Form with {$form_id} is not found.");
	}
	
	if($form_state['method'] == "POST"||$form_state['#api']=='true')
	{
		$form = drupal_process_form( $form, $form_state );
	}
	
	$markup = clickpdx_render_form( $form, $form_state );
	
	return $markup;
}


function theme_form_textarea_element(&$vars)
{
	$elem = $vars['elem'];
	$id = isset( $elem['#id'] ) ? "id='{$elem['#id']}'" : NULL;
	$name = isset( $elem['#name'] ) ? $elem['#name'] : $vars['name'];
	$rows = isset( $elem['#rows'] ) ? " rows='{$elem['#rows']}'" : NULL;
	$cols = isset( $elem['#cols'] ) ? " cols='{$elem['#cols']}'" : NULL;
	$placeholder = isset( $elem['#placeholder'] ) ? "placeholder='{$elem['#placeholder']}'" : NULL;
	
	$output = "<textarea {$placeholder} {$id} name='{$name}' $cols $rows></textarea>";
	
	if( $elem['#error'] === TRUE )
	{
		$vars['#markup'] = "<div class='error'><label>{$title}</label>" . $output . $elem['#error_message'] . '</div>';
	} else {
		$vars['#markup'] = "<div><label>{$title}</label>" . $output . "</div>";
	}
	return $vars['#markup'];
}


function theme_form_email_element(&$vars)
{
	$elem = $vars['elem'];
	$id = isset( $elem['#id'] ) ? "id='{$elem['#id']}'" : NULL;
	$name = isset( $elem['#name'] ) ? $elem['#name'] : $vars['name'];
	$placeholder = isset( $elem['#placeholder'] ) ? "placeholder='{$elem['#placeholder']}'" : NULL;
	
	$output = "<input type='{$elem['#type']}' {$placeholder} {$id} value='{$elem['#value']}' name='{$name}' />";
	
	if( $elem['#error'] === TRUE )
	{
		$vars['#markup'] = "<div class='error'><label>{$title}</label>" . $output . $elem['#error_message'] . '</div>';
	} else {
		$vars['#markup'] = "<div><label>{$title}</label>" . $output . "</div>";
	}
	return $vars['#markup'];
}


/**
 * @function theme_form_element
 * @description - works with standard form elements like hidden / text
 **/
function theme_form_element( &$vars ) {
	$elem = $vars['elem'];
	$title = $elem['#title'];
	$id = isset( $elem['#id'] ) ? "id='{$elem['#id']}'" : NULL;
	$name = isset( $elem['#name'] ) ? $elem['#name'] : $vars['name'];
	$placeholder = isset( $elem['#placeholder'] ) ? "placeholder='{$elem['#placeholder']}'" : NULL;
	
	$output = "<input type='{$elem['#type']}' {$placeholder} {$id} value='{$elem['#value']}' name='{$name}' />";
	
	if( $elem['#error'] === TRUE ) {
		$vars['#markup'] = "\n\t<div class='error'><label>{$title}</label>" . $output . $elem['#error_message'] . '</div>';
	} elseif (!empty($title)) {
		$vars['#markup'] = "<div>\n\t<label>{$title}</label>" . $output."</div>";
	} else $vars['#markup'] = "\n\t<div>".$output . "</div>";
	
	return $vars['#markup'];
}


function theme_form_element_hidden( &$vars ) {
	$elem = $vars['elem'];
	$title = $elem['#title'];
	$id = isset( $elem['#id'] ) ? "id='{$elem['#id']}'" : NULL;
	$name = isset( $elem['#name'] ) ? $elem['#name'] : $vars['name'];
	
	$output = "<div><input type='{$elem['#type']}' {$id} value='{$elem['#value']}' name='{$name}' /></div>";
	
	$vars['#markup'] = $output;

	return $vars['#markup'];
}


function theme_form_checkbox_element( &$vars ) {
	$output = '';
	$default = &$vars['elem']['#default_value'];
	$options = &$vars['elem']['#options'];

	foreach( $options AS $k=>$v ) {
		$checked = $v == $default ? "checked='checked'" : null;
		$output .= "<input type='checkbox' {$checked} value='{$k}' /><label>{$v}</label><br />";
	}
	$vars['#markup'] = "<div class='checkboxes'>".$output ."</div>";
	return $vars['#markup'];
}

function theme_form_select_element( &$vars ) {
	$output = '';
	$default = &$vars['elem']['#default_value'];
	$options = &$vars['elem']['#options'];

	foreach( $options AS $k=>$v ) {
		$checked = $v == $default ? "selected='selected'" : null;
		$output .= "<option value='{$v}' {$checked}>{$v}</option>";
	}
	$vars['#markup'] = "<select name='{$vars['name']}'>".$output ."</select>";
	return $vars['#markup'];
}

function clickpdx_render_form( $form, &$form_state ) {
	$output = "";
	
	// loop through the form elements and call their respective theme functions
	foreach( $form AS $name => &$elem ) {
		if( strpos( $name, '#' ) === 0 ) continue; // skip form attributes
		$theme = "theme_" . $elem['#theme'];
		if( function_exists( $theme ) )
			$elem['#markup'] = theme( $elem['#theme'], array( 'elem'=>$elem, 'name' => $name ) );
		else $elem['#markup'] = theme( 'form_element', array( 'elem'=>$elem, 'name' => $name ) );
		
		$output .= $elem['#markup'];
	}
	// finally, fetch the completely themed form and return it
	$output = theme( 'form', array( 'form' => $form, 'form_id' => $form['#form_id'], 'content' => $output ) );
	return $output;
}


/**
 * @function - clickpdx_render
 * @description - render an element
 * // use the element's theme function
 * // or use the default theme function for that element
 * // somehow this function knows which variables to pass to the
 * // specific element's theme function
 * // return the output from the theme function or template
 **/
function clickpdx_render( $elem ) {
	$type = $elem['#type'];
	$theme = $elem['#theme'];
}