<?php 
/*
Plugin Name: Site Plugin Core
Plugin URI: http://positivesum.org/wordpress/site-plugin-core
Description: Library that can be used to create Site Plugins. Site plugins simplify iterative development process.
Version: 0.2.6
Author: Taras Mankovski
Author URI: http://taras.cc
*/

include_once(dirname(__FILE__).'/lib.php');
include_once(dirname(__FILE__).'/upgrade.php');
session_start();
# display Site Plugin Admin interface
if ( is_admin() ) include_once(dirname(__FILE__).'/admin.php');

if (!class_exists("SitePlugin")) {
	
	class SitePlugin {
		
		function __construct($name) {
	
			$this->name = $name;
			$this->slug = sanitize_title($name);
			$this->version_option = $this->slug.'_version';
            $this->changelog_option = $this->slug.'_changelog';
			
			$this->path = WP_PLUGIN_DIR . '/' . $this->slug;			
			
			$this->h2o = new H2o(NULL, array('context', $this));
			$this->errors = new WP_Error;

			// contains path to versions
			$this->versions = $this->path . '/versions/';
			
			add_action('init', array(&$this, 'init'));
			
			register_activation_hook( $this->path.'/plugin.php' , array(&$this, 'create_version_option') );
            register_deactivation_hook( $this->path.'/plugin.php' , array(&$this, 'delete_version_option') );
			
		}
		
		/**
		 * Initialization function that setups admin
		 *
		 */
		function init() {
			
			# setup admin menu
			if ( is_admin() ) {
				
				define('SITEPLUGIN', true);
				
				add_action('admin_menu', array(&$this, 'setup_menu'));
				
			}
			
			do_action('site_plugin_init');

		}	
		
		/**
		 * Creates version option for current plugin
		 */
		function create_version_option() {
			if ( get_option($this->version_option, false) === false ) { // if option does not exist
				add_option($this->version_option, 0);
			}
            update_option($this->changelog_option, array());
		}
        /**
         * Deletes version option for current plugin
         * @return void
         */
        function delete_version_option() {
			delete_option($this->version_option);
            delete_option($this->changelog_option);
		}
		/*
		 * Return the current active version of the plugin
		 * @return int current version number
		 */
		function get_current_version() {
			
			return get_option($this->version_option, 0 );
			
		}
		
		/*
		 * Increase next version by 1
		 * @return int next version
		 */
		function bump_version() {
			
			$next = $this->get_current_version() + 1;
			update_option($this->version_option, $next);
			
			return $next;
			
		}
		
		/*
		 * Return array of all versions and their info
		 * @param bool applied do you want applied versionly only? 
		 * @return array of all versions and their info
		 */
		function get_versions($applied=false) {
			
			$versions = array();
			$directory = $this->versions;
			if ( is_dir($directory) ) {
				if ($dh = opendir($directory)) {
			        while (($file = readdir($dh)) !== false) {
			        	if ( filetype($directory . $file) == 'dir' && is_numeric($file) ) {
			        		$versions[(int)$file] = $this->get_version_info((int)$file);	
			        	}
			        }
        			closedir($dh);
				} else {
					wp_die('Versions directory does not exist in ' . $this->name . ' plugin directory');
				}
			}
			ksort($versions);
			if ( $applied ) {
				$versions = array_slice($versions, 0, $this->get_current_version()); 
			}
			return $versions;
			
		}
		
		/*
		 * Return array of information about a specific version
		 * @param $id int id of a version to load
		 * @return array of information about a specific version
		 */
		function get_version_info( $id ) {
			
			$version = array();
			
			$version['upgrade'] = $this->get_path($id, 'upgrade');
//			$version['version'] = $this->get_path($id, 'version');
				
			return $version;
		}

		function get_path($id, $name) {
			$file = $this->versions."$id/$name.php";
			if ( file_exists($file) ) {
				$value = $file;
			} else {
				$value = false;
			}
			return $value;
		}
		
		/*
		 * Return weather or not an upgrade is available
		 * @return bool of available upgrades
		 */
		function is_upgrade_available() {
			return count($this->available_upgrades()) > 0;
		}
		
		/*
		 * Return array of available upgrade versions
		 * @return array of available upgrades
		 */
		function available_upgrades() {

			$current = $this->get_current_version();
			$versions = $this->get_versions();
			if ( $current == 0 ) {
				return $versions;
			}
			
			$ids = array_keys($versions);
			if ( in_array($current, $ids) ) {
				return array_slice($versions, array_search($current, $ids)+1, sizeof($ids), TRUE);
			} else {
				$this->errors->add('error', "Something went wrong: $current version is not available, therefore upgrade could not be determined.");
			}
			
		}		
		
		/*
		 * Return the version number of the next upgrade
		 * @return int version of the next upgrade
		 */
		function next_upgrade() {
			return $this->get_current_version() + 1;
		}
		
		/*
		 * Return id of the last version available
		 * 
		 * @return int id of last version
		 */
		function last_version() {
			$versions = $this->get_versions();
			$ids = array_keys($versions);
			return (int)end($ids);
		}
		
		/*
		 * Return id of the next version
		 * 
		 * @return int id of the next version
		 */
		function next_version() {
			return $this->last_version() + 1;
			
		}
		
		/*
		 * Perform the upgrade.
		 * if action = before, runs in dryrun mode
		 * @param $id int id of the version to upgrade to
		 * @return mixed bool or array
		 */
		function execute($id) {
			
			$next = $this->next_upgrade();
			if ( $id != $next ) {
				return "Next upgrade is " . $this->next_upgrade() . " not $id";
			}
			
			$version = $this->get_version_info($id);			
			$upgrade = null;
			switch($_GET['action']):
			case 'before':
                $upgrade = new SiteUpgrade($this->errors, true);
                include($version['upgrade']);
                break;
			case 'upgrade':
                $upgrade = new SiteUpgrade($this->errors);
                include($version['upgrade']);
                $_SESSION['upgrade'] = $upgrade;
                break;
//			case 'after':
//                include($version['after']);
//                $this->bump_version();
//                break;
			case 'apply':
                $upgrade = $_SESSION['upgrade'];
                $result = $upgrade->execute();
                if ( $result && !is_wp_error($result)) { // TODO or not an object of WP_ERROR
                    $this->bump_version();
                }
                $change_log = get_option($this->changelog_option);
                if ($change_log === null OR $change_log === '')
                    $change_log = array();
                array_push($change_log, $upgrade -> changelog);
                update_option($this->changelog_option, $change_log);
                unset($_SESSION['upgrade']);
                break;
			default:
                wp_die($_GET['action'] . ' is not a valid action.');
			endswitch;
			
			return TRUE;
		}
		
		/**
		 * Callback to setup 
		 */
		function setup_menu(){
			
			$menu_slug =  $this->slug.'-plugin';
			add_menu_page(__($this->name), __($this->name), 'manage_options', $menu_slug, array(&$this, 'main_page'));
			if ( $this->is_upgrade_available() ) {
				add_submenu_page($menu_slug, __('Available Upgrades'), __('Upgrade'), 'manage_options', $menu_slug.'-upgrade', array(&$this, 'upgrade_page') );				
			}
			add_submenu_page($menu_slug, __('Create Upgrade'), __('Create Upgrade'), 'manage_options', $menu_slug.'-create-upgrade', array(&$this, 'create_upgrade_page') );
			add_submenu_page($menu_slug, __('Create Upgrade'), __('Run Tests'), 'manage_options', $menu_slug.'-run-tests', array(&$this, 'run_tests_page') ); // Alexander
		}

		/*
		 * Creates next version in versions directory
		 * 
		 * @return int id of the created version
		 */
		function create_version() {
			
			$next = $this->next_version();
			$previous = $this->last_version();
			$version_dir = $this->versions . "$next/";
			
			if ( !mkdir($version_dir) ) return $this->errors->add('error', sprintf('Could not create version %s in %s', $next, $this->versions));

			$this->h2o->loadTemplate(WP_PLUGIN_DIR . '/site-plugin-core/templates/upgrade.php');
			
			$upgrades = apply_filters('site_upgrade_generate', '');
			
			$code = $this->h2o->render(array('upgrades'=>$upgrades));
			
			$output_file = fopen($version_dir.'upgrade.php', 'w');
			fwrite($output_file, $code);
			fclose($output_file);
			
			return $next;
		}

		/*
		 * This page shows information about tests results.
		 */
		function run_tests_page() { 
			$this->verify_permissions(); 

			$pluginDir		= basename(dirname(__FILE__));
			$pluginPath		= WP_PLUGIN_DIR . '/' . $pluginDir;
			$pluginUrl 		= WP_PLUGIN_URL.'/'.$pluginDir;					
			
			$url = $pluginUrl . '/phprack.php';
			if( $curl = curl_init($url) ){
				// 
				curl_setopt($curl, CURLOPT_URL,$url);						
				curl_setopt($curl, CURLOPT_FAILONERROR, 1); 
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);// allow redirects  			
				curl_setopt($curl, CURLOPT_RETURNTRANSFER,1); // return into a variable  			
				curl_setopt($curl, CURLOPT_POST, 1); // set POST method  			
				$data = array ('plugin_dir' => $pluginPath.'/rack-tests');				
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				$out = curl_exec($curl);
				curl_close($curl);
				echo $out;
			}	

		}
		
		/*
		 * This page shows information about creating new version and link to do it.
		 */
		function create_upgrade_page() { 
			$this->verify_permissions(); 

			if ( $created = array_key_exists('action', $_POST) && $_POST['action'] == 'create' ) {
				$version = $this->create_version();
			} else {
				$version = NULL;
			}
			
			$elements = apply_filters('site_plugin_admin', array());
			
			$values = array(
				'created'=>$created, 
				'version'=>$version,
				'url'=>$_SERVER['REQUEST_URI'],
				'elements'=>$elements
			);
			
			$h2o = new h2o(WP_PLUGIN_DIR.'/site-plugin-core/views/create_upgrade.html');
			echo $h2o->render($values);
		}
		
		function verify_permissions() {
			if ( !current_user_can('manage_options') ) {
      			wp_die( __('You do not have sufficient permissions to access this page.') );
    		}		
		}
		
		function main_page() {
			$this->verify_permissions();
			# TODO: load main page from template
            include('views/main.html');

		} 
		
		function upgrade_page() { 
			$this->verify_permissions();
			# TODO: load upgrade page from template
            include('views/upgrade.html');
			
		}
	}
}

if ( !class_exists('SiteVersion_0') ) {

	/*
	 * This is an abstract class for future site versions.
	 */
	class SiteVersion_0 {
		
		var $version = 0;
		
	}
	
}

?>
