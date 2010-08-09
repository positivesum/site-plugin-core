<?php 
/*
 * This file is executed before performing the upgrade. This can be used to verify that
 * pre upgrade conditions match those of expected.
 * 
 * For example, if you are going to add a category as a child to an existing category then
 * you would use this file to verify that the parent category exists.
 *
 * I would recommend writing these tests before writing the upgrade code.
 * 
 */
defined('SITEPLUGIN') or wp_die('This script can only be executed from inside wp-admin');

# verify Hello world! post is present
assert(get_post($id = 1));

# verify that about us page is present
assert(get_post($id = 2));

# verify that Mr Wordpress comment is present
assert(get_comment($id = 1));

# verify that all of the links are present
assert($link = get_link($id=1));
assert($link->link_name=='Documentation');
assert($link = get_link($id=2));
assert($link->link_name=='Development Blog');
assert($link = get_link($id=3));
assert($link->link_name=='Suggest Ideas');
assert($link = get_link($id=4));
assert($link->link_name=='Support Forum');
assert($link = get_link($id=5));
assert($link->link_name=='Plugins');
assert($link = get_link($id=6));
assert($link->link_name=='Themes');
assert($link = get_link($id=7));
assert($link->link_name=='WordPress Planet');

# remove hello.php plugin
assert(is_plugin_active('hello.php') === FALSE);

?>