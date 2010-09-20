<?php 

// include spyc - yaml parsing library - http://code.google.com/p/spyc/
if ( !class_exists('Spyc') ) require_once( dirname(__FILE__).'/lib/spyc.php');

// include h2o - template parcing library
if ( !class_exists('H2o') ) require_once(dirname(__FILE__).'/lib/h2o.php');

// include SitePlugin custom h2o filters
require_once(dirname(__FILE__).'/lib/h2o_filters.php');

// include Site Upgrade Actions
include_once(dirname(__FILE__).'/upgrade-action.php');
include_once(dirname(__FILE__).'/actions/option.php');
include_once(dirname(__FILE__).'/actions/category.php');// TODO if category is above option and if we generate only category then it is not generated
//include_once(dirname(__FILE__).'/actions/page.php'); TODO This somehow overrides category.php
include_once(dirname(__FILE__).'/actions/theme.php');

// include tests when test is being executed
if ( defined('DIR_TESTPLUGINS') && DIR_TESTPLUGINS ) {

	$base = dirname(__FILE__).'/tests';
	$tests = glob($base.'/*.php');
	foreach ($tests as $test)
		include_once($test);

}

?>