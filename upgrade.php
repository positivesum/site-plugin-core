<?php 

if ( !class_exists('SiteUpgrade') ) {
	
	class SiteUpgrade {
		var $dryrun    = false;
		var $tasks     = array(); # contains tasks that need to be executed during this upgrade
		var $changelog = array(); # contains changelog for current upgrade
		var $actions   = array(); # contains all of the actions that this upgrade is aware of
		
		/*
		 * @param $errors WP_Error instance for reporting errors
		 */
		function __construct($messages, $dryrun = false) {
			
			$this->messages =& $messages;
			$this->dryrun = $dryrun;

            /*
             * site_upgrade_actions hook loads $this->actions property with an associative array.
             * This array has action name as key and callback in the following form:
             * array("$actionname" => array(&$instance_of_SiteUpgradeAction, "$function_name"), ...)
             */
			$this->actions = apply_filters('site_upgrade_actions', $this->actions);

		}
		
		function get_arg_array($arg) {

			if ( !is_array($arg) && !is_string($arg) ) {
				$this->messages->add('error', __('Invalid argument type: ') . gettype($arg) );
				return $this->messages;
			} elseif ( is_string($arg) ) {
				return SiteUpgradeAction::unserialize($arg);
			} elseif ( is_array($arg) ) {
				return $arg;
			}

		}
		/*
		 * Add a task to the current upgrade.
		 * @param str name of callback function to execute
		 * @param array or yaml stirng to pass as argument
		 * @param str message to describing this action
		 * @return TRUE or WP_Error
		 */
		function add($function, $arg, $msg ) {

			$arg = $this->get_arg_array($arg);
			array_push($this->tasks, array($function, $arg, $msg ));
			if ( $msg ) array_push($this->changelog, $msg);
            $this->messages->add('info', "The upgrade statement has been queued. Function: $function");
			return true;

		}
		
		/*
		 * Execute site upgrade
		 * @param boolean execute change in dryrun mode
		 * @return TRUE or WP_Error
		 */
		function execute( $dryrun = false ) {
			
			do_action('site_plugin_before_upgrade');
			$errors = array();
			
			foreach ( $this->tasks as $task ) {
				
				list($function, $arg, $msg) = $task;
				
				if ( array_key_exists($function, $this->actions) ) { 
					try {
//	                	if ( $dryrun ) {
//	                		$this->messages->add('info', __('Dryrun: ') . $msg);
//	                	} else {
	                		$action = $this->actions[$function];
	                		if ( is_wp_error( $result = call_user_func($action, $arg, $msg) )) {
		                		array_push($errors, $result->get_error_message());
	                		} else {
	                			$this->messages->add('info', $msg);
	                		}
//	                	}
	            	} catch (Exception $e) {
	                	$this->messages->add('exception', $e->getMessage());
	            	}
				} else {
					array_push($errors, __("Function is not defined: ") . $function);
				}
				
			}

			do_action('site_plugin_after_upgrade');
			
			if ( $errors ) { 
				foreach ( $errors as $error ) {
					$this->messages->add('error', $error);
				}
				return $this->messages;
			}
			
			return true;
		}

		/*
		 * Returns true if assertion succeds and add the to the message queue
		 * @param $test boolean result of the test
		 * @param $msg string to report
		 * @return boolean result of assertion
		 */
		function verify($function, $arg ) {
			switch ($this->dryrun) {
                case true:
                    if (array_key_exists($function, $this->actions) ) {
                        $action = $this->actions[$function];
                        $arg = $this->get_arg_array($arg);
                        $result = call_user_func($action, $arg);
                        $result = $result == '' ? 'false' : 'true';
                        $this->messages->add('info', "Function: $function, Arguments: $arg[0], Return value:  $result");
                    } else {
                        array_push($errors, __("Function is not defined: ") . $function);
                    }
                    return false;
                case false:
                    return true;
            }
            
		}		
	}
}

?>