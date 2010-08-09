<?php 
/*
 * This is the main file that performs all of the upgrades.
 * 
 * In this file,  you can do things like change page parents, perform large database changes
 * or anything else that would require interaction with the admin interface.
 * 
 * Please, include a comment for each action. A example of such a comment is included below. 
 * These comments are used to generate the changelog for this site.
 */
defined('SITEPLUGIN') or wp_die('This script can only be executed from inside wp-admin');

/* remove Hello world post */
wp_delete_post($id = 1, TRUE);

/* remove about us page */
wp_delete_post($id = 2, TRUE);

/* remove Mr Wordpress comment
wp_delete_comment($id = 1);

/* remove all links */
wp_delete_link($id=1);
wp_delete_link($id=2);
wp_delete_link($id=3);
wp_delete_link($id=4);
wp_delete_link($id=5);
wp_delete_link($id=6);
wp_delete_link($id=7);


/* remove hello.php plugin */
unlink(WP_PLUGIN_DIR.'/hello.php');


?>