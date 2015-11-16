<?php
define('CORE_AUTOLOADER_EXTENSION','php');

/**
 * Core autoloader.
 *
 * Responsible for loading most core classes.
 */
$core=createAutoloader(array('core/lib'),DRUPAL_ROOT);



spl_autoload_register($core,true,false);