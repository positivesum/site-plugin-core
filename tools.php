<?php
/**
 *
 * @author Valera Satsura (http://www.odesk.com/users/~~41ba9055d0f90cee)
 * @copyright Positive Sum (http://positivesum.org)
 */

if (!class_exists('SitePluginTools')) {
	class SitePluginTools {

		public $messages = array();

		public function __construct() {
			add_action('init', array(&$this, 'init'));
		}

		public function init() {
			global $pagenow;

			// Run some tools only on page wp-login.php
			if ($pagenow == 'wp-login.php') {
				// Load auto add/delete users tools
				$this->auto_users();

				// Show messages in login box form
				add_action('login_message', array(&$this, 'message'));

			}
		}

		/**
		 * Show messages
		 *
		 * @return str
		 */
		public function message($message) {
			$message .= implode("\n", $this->messages);
			if (strlen($message)) {
				return '<p class="message">'.$message.'</p>';
			}
			return '';
		}

		/**
		 * Parse strings like "var1=val1,var2=val2,..."
		 *
		 * @param  $string
		 * @return array
		 */
		private function pretty_unserialize($string) {
			$result = array();
			$values = array_values(array_filter(explode(',', $string)));

			foreach ($values as $item) {
				$parts = array_values(array_filter(explode('=', $item)));
				$result[$parts[0]] = $parts[1];
			}

			return $result;
		}

		/**
		 * Add/Delete users automatical
		 * @return void
		 */
		function auto_users() {
			global $wp_roles, $wpdb;

			// Load some admin functions
			include(ABSPATH.'/wp-admin/includes/user.php');

			if (false == isset($wp_roles)) {
				$wp_roles = new WP_Roles();
			}

			// Option Name
			$option_base = 'site-plugin-core-users';

			// Constant prefix
			$prefix = 'SITE_ADD_';

			// Get list of roles
			// Read http://codex.wordpress.org/Roles_and_Capabilities#Roles
			$roles = array_keys($wp_roles->roles);

			// Check all roles
			foreach ($roles as $role) {
				// Make constant name
				$_const = $prefix . strtoupper($role) . "S";

				// Option name for defined const
				$option_name = $option_base . '-' . strtolower($role);

				// Get option
				$option_value = get_option($option_name);

				// Cached users
				$old_users = $this->pretty_unserialize($option_value);

				// If constant not defined,
				// delete users and
				// move to next constant
				if (!defined($_const)) {
					// Remove users
					foreach ($old_users as $email => $hash) {
						if ($user_id = email_exists($email)) {
							$this->messages[] = "<b>Deleted:</b> $email";
							wp_delete_user($user_id);
						}
					}

					// Delete option
					delete_option($option_name);

					// Go to other defined options
					continue;
				}

				// Get string from constant
				$const_value = constant($_const);

				// If cached string (db) and constant string equal
				// not do any actions
				if ($const_value == $option_value) {
					continue;
				}

				// New users
				$new_users = $this->pretty_unserialize($const_value);

				// Need delete this users
				$del_users = array_diff_assoc($old_users, $new_users);

				// Delete users
				foreach ($del_users as $email => $hash) {
					if ($user_id = email_exists($email)) {
						$this->messages[] = "<b>Deleted:</b> $email";
						wp_delete_user($user_id);
					}
				}

				// Need adding this users
				$add_users = array_diff_assoc($new_users, $old_users);

				// Add new users
				foreach ($add_users as $email => $hash) {
					// Make array
					$_user = array('user_pass' => '', 'user_email' => $email, 'user_login' => $email, 'role' => $role);
					$user_id = wp_insert_user($_user);
					$wpdb->query("UPDATE $wpdb->users SET user_pass = '" . $hash . "' WHERE ID = $user_id");
					$this->messages[] = "<b>Added:</b> $email";
				}

				// Update option
				update_option($option_name, $const_value);
			}
		}
	}

	// Make global instance of site plugin tools
	global $site_tools;
	$site_tools = new SitePluginTools();
}

 
