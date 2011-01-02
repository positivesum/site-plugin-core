<?php
// this param is mandatory, others are optional
$url = 'rack-tests';

if (isset($_REQUEST['plugin_dir']) && $_REQUEST['plugin_dir'] != '') {
	$url = $_REQUEST['plugin_dir'];
}
$phpRackConfig = array('dir' => $url);

// configure wp
//error_reporting(E_ALL & ~E_STRICT);
error_reporting(0);
define('DIR_WP', realpath(dirname(__FILE__).'/../../../'));			
require_once(DIR_WP.'/wp-config.php');						
require_once(ABSPATH .'wp-admin/includes/plugin.php');
require_once(ABSPATH .'wp-includes/taxonomy.php');
require_once(ABSPATH .'wp-admin/includes/taxonomy.php');							
require_once(ABSPATH .'wp-content/plugins/site-plugin-core/actions/category.php');	
require_once(ABSPATH .'wp-content/plugins/site-plugin-core/actions/option.php');

// absolute path to the bootstrap script on your server
include 'phpRack/bootstrap.php';
