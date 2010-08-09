<?php 
/*
 * This file contains version class for this version.
 * Version class inherits from previous version class.
 * This site version can be used to enable functionality on the site and maintain this functionality
 * from version to version.
 */

{% if previous !=  0 %}
include_once(WP_PLUGIN_DIR . "{{ previous_path }}");
{% endif %}

class SiteVersion_{{ next }} extends SiteVersion_{{ previous }} {
	var $version = {{ next }};	
}

?>