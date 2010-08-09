<?php 
/*
 * This file is executed after performing the upgrade. This can be used to verify that
 * the changes peformed as expected.
 * 
 * If this these tests are going to be used continuously to test the site,
 * then you should put them in pregression_tests.php instead.
 * 
 */
defined('SITEPLUGIN') or wp_die('This script can only be executed from inside wp-admin');

# verify that Hello world! post was deleted
assert(!get_post($id = 1));

# verify that About Us page was deleted
assert(!get_post($id = 2));

# verify that Mr Wordpress comment was deleted
assert(!get_comment($id = 1));

# verify that all links were removed
assert($link = get_link($id=1));
assert($link = get_link($id=2));
assert($link = get_link($id=3));
assert($link = get_link($id=4));
assert($link = get_link($id=5));
assert($link = get_link($id=6));
assert($link = get_link($id=7));

# verify that hello.php plugin was removed
assert(!file_exists(WP_PLUGIN_DIR.'/hello.php'));


?>