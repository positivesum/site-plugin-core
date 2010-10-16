<?php 
if ( !class_exists('SitePluginAdmin') ) {

	class SitePluginAdmin {
		
		function __construct() {
			$loader = new H2o_File_Loader(dirname(__FILE__).'/views/');
			$this->h2o = new H2o(NULL, array('context'=>&$this, 'loader'=>$loader));
			$this->h2o->addFilter('WordpressFilters');
			$this->messages = new WP_Error;
			
		}
		
		function init() {
	
			add_action('admin_menu', array($this, 'admin_menu'));
	
		}
		
		function admin_menu() {
			# create management page in settings
			add_options_page(__('Site Plugin Settings'), __('Site Plugin'), 'manage_options', 'site_plugin_settings', array($this, 'settings_page') );				
		}

		/*
		 * Creates empty site plugin
		 * @param name str name of plugin to create
		 * @return name of plugin directory or WP_Error
		 */
		function create_plugin($name) {
			
			$plugin = sanitize_title($name);
			$path = WP_PLUGIN_DIR . '/' . $plugin;
			$plugin_path = $path . '/plugin.php';
			
			if ( !file_exists($path) ) {
				if ( mkdir($path) ) {
					
					$h2o = new h2o(dirname(__FILE__) . '/templates/plugin.php');			
					$contents = $h2o->render(array('name'=>$name));
					
					$plugin_file = fopen($path.'/'.'plugin.php', 'w');
					fwrite($plugin_file, $contents);
					fclose($plugin_file);
					mkdir( $path.'/versions' );
				} else {
					$this->messages->add('error', __("Could not create path: ".$path));
					return $this->messages;
				}
			} else {
				$this->messages->add('error', __("Plugin directory: ").$path.__(' already exists. Choose another name.'));
				return $this->messages;
			}
			$this->messages->add('info', __('Plugin ').$name.__(' was created successfully.'));
			return $plugin;
			
		}
		
		/*
 		 * Displays settings admin page
 		*/
		function settings_page() {

			$created = FALSE;
			$error = FALSE;
            $name = '';
            $plugin_file = '';
            $activation_url = '';
            
			$messages =& $this->messages;
			if ( array_key_exists('name', $_POST ) ) {
				if ( $name = $_POST['name'] ) {
					$error = is_wp_error( $result = $this->create_plugin($name) );
					if ( !$error ) {
						$created = TRUE;
						$plugin = $result;
						$plugin_file = "$plugin/plugin.php";
                        $nonce= wp_create_nonce('activate-plugin_'.$plugin_file);
						$activation_url = get_bloginfo('wpurl')."/wp-admin/plugins.php?action=activate&plugin=$plugin_file&_wpnonce=$nonce";
					}
				} else {
					$error = TRUE;
					$messages->add('error', __('Plugin name field can not be empty.'));
				}
			}
			$this->h2o->loadTemplate('settings.html');
			$context = array(
				'error'=>$error, 
				'created'=>$created, 
				'name'=>$name, 
				'plugin_file'=>$plugin_file,
				'activation_url'=>$activation_url,
				'messages'=>$messages,
				'url'=>$_SERVER['REQUEST_URI']
				);
			echo $this->h2o->render($context);
		}				
	}	
}

$site_plugin_admin = new SitePluginAdmin;
add_action('init', array($site_plugin_admin, 'init'));	
?>