<?php
/**
 * Core includes
 * 
 * Core includes are required before any additional 
 * processing of the router can be done.
 */
require_once DRUPAL_ROOT .'/core/includes/autoload.inc';
require_once DRUPAL_ROOT .'/core/autoload.php';
require_once DRUPAL_ROOT .'/vendor/autoload.php';

require( DRUPAL_ROOT.'/core/includes/errors.inc' );
require( DRUPAL_ROOT.'/core/includes/database.inc' );
require( DRUPAL_ROOT.'/core/includes/session.inc' );
require( DRUPAL_ROOT.'/core/includes/string.inc' );
require( DRUPAL_ROOT.'/core/includes/file.inc' );
require( DRUPAL_ROOT.'/core/includes/download.inc' );
require( DRUPAL_ROOT.'/core/includes/system.inc' );
require( DRUPAL_ROOT.'/core/includes/module.inc' );
require( DRUPAL_ROOT.'/core/includes/menu.inc' );
require( DRUPAL_ROOT.'/core/includes/form.inc' );
require( DRUPAL_ROOT.'/core/includes/utilities.inc' );
require( DRUPAL_ROOT.'/core/includes/theme.inc' );
require( DRUPAL_ROOT.'/core/includes/server.inc' );
require( DRUPAL_ROOT.'/core/includes/filters.inc' );
require( DRUPAL_ROOT.'/core/includes/node.inc' );
require( DRUPAL_ROOT.'/core/includes/user.inc' );
require( DRUPAL_ROOT.'/core/includes/html.inc' );



require(DRUPAL_ROOT.'/vendor/clickpdx/ocdla/database/DBQuery.php');

/**
 * Settings file.
 *
 * Load the appropriate settings file for this request.
 */
processSettings();
require(getSettingsFile());


// Perform a database connection
$db_connection = get_resource('default');



/**
 * Load modules.
 *
 * Load modules from the database.  We process
 * the .module files.
 */
_drupal_load_modules(true);



/**
 * Site maintenance.
 *
 * Check if maintenance.html exists.  If it does then the site
 *	+ is under maintenance and we should return the appropriate HTTP header.
 */
if(site_maintenance()) clickpdx_server_maintenance();

function new_contact($contact_id)
{
	return new Contact( $contact_id );
}