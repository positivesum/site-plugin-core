<?php 
/*
Plugin Name: {{ name }}
Plugin URI: http://positivesum.org/wordpress/site-plugin-core
Description: Site Plugin
Version: 0.1
Author: Taras Mankovski
Author URI: http://taras.cc
*/

if ( is_admin() ) {
	
	# make sure that SitePlugin code is loaded
	if ( !class_exists('SitePlugin') ) {
		include_once(WP_PLUGIN_DIR.'/site-plugin-core/plugin.php');
	}
	
	# initialize plugin
	new SitePlugin('{{ name }}');
	
}

?>
