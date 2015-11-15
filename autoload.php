<?php
define('CORE_AUTOLOADER_EXTENSION','php');

/**
 * Core autoloader.
 *
 * Responsible for loading most core classes.
 */
$core=createAutoloader(array('core/lib'),DRUPAL_ROOT);


/**
 * Doctrine autoloader.
 *
 * Responsible for doing a stand-alone loading of Doctrine.
 */
$doctrine=createAutoloader(array('common/lib','dbal/lib'),DRUPAL_ROOT.'/vendor/doctrine');




spl_autoload_register($core,true,false);
spl_autoload_register($doctrine,true,false);