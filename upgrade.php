<?php 

if ( !class_exists('SiteUpgrade') ) {
	
	class SiteUpgrade {
		
		var $tasks = array(); # contains tasks that need to be executed during this upgrade
		var $changelog = array(); # contains changelog for current upgrade
		var $actions = array(); # contains all of the actions that this upgrade is aware of
		
		/*
		 * @param $errors WP_Error instance for reporting errors
		 */
		function __construct($messages) {
			
			$this->messages =& $messages;
			
			$this->actions = apply_filters('site_upgrade_actions', $this->actions);
			
		}
		
		/*
		 * Add a task to the current upgrade.
		 * @param str name of callback function to execute
		 * @param array or yaml stirng to pass as argument
		 * @param str message to describing this action
		 * @return TRUE or WP_Error
		 */
		function add( $function, $arg, $msg ) {
	
			if ( !is_array($arg) && !is_string($arg) ) {
				$this->messages->add('error', __('Invalid argument type: ') . gettype($arg) );
				return $this->messages;
			} elseif ( is_string($arg) ) {
				$arg = Spyc::YAMLLoad($arg);
			} elseif ( is_array($arg) ) {
				$arg = $arg;
			}
			
			array_push($this->tasks, array( $function, $arg, $msg ));
			if ( $msg ) array_push($this->changelog, $msg);
			
			return TRUE;
		}
		
		/*
		 * Execute site upgrade
		 * @param boolean execute change in dryrun mode
		 * @return TRUE or WP_Error
		 */
		function execute( $dryrun = FALSE ) {
			
			do_action('site_plugin_before_upgrade');
			
			$errors = array()
			
			foreach ( $this->tasks as $task ) {
				
				list($function, $arg, $msg) = $task;
				
				if ( array_key_exists($this->actions, $function) ) { 
					try {
	                	if ( $dryrun ) {
	                		$this->messages->add('info', __('Dryrun: ') . $msg);
	                	} else {
	                		$action = $this->actions[$function];
	                		if ( is_wp_error( $result = $action->execute($args, $this->messages) ) {
		                		$this->messages->add('info', $msg);	                			
	                		} else {
	                			array_push($errors, $return->get_error_message());
	                		}
	                	}
	            	} catch (Exception $e) {
	                	$this->messages->add('exception', $e->getMessage());
	            	}
				} else {
					array_push($errors, __("Function is not defined: ") . $function));
				}
				
			}

			do_action('site_plugin_after_upgrade');
			
			if ( $errors ) { 
				foreach ( $errors as $error ) {
					$this->messages->add('error', $error);
				}
				return $this->messages;
			}
			
			return TRUE;
		}

		/*
		 * Returns true if assertion succeds and add the to the message queue
		 * @param $test boolean result of the test
		 * @param $msg string to report
		 * @return boolean result of assertion
		 */
		function verify( $test, $msg ) {
			
			# TODO: Fix this, this is not right.
			$test = (boolean) $test;
			
			if ( $test ) {
				$this->messages->add('passed', $msg);
			} else {
				$this->messages->add('failed', $msg);
			}
			
			return $test;
		}		
		
	}
	
}

?>