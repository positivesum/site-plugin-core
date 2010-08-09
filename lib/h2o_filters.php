<?php 

if ( class_exists('h2o') ) {
	
	h2o::addFilter('WordpressFilters');
	class WordpressFilters extends FilterCollection {
		
		/*
		 * Wordpress localization filter to use inside of h2o templates
		 * @param str test to localize
		 * @return str localized string
		 */
		function l($text) {
			return __($text);
		}
		
		/*
		 * Get single error message. This will get the first message available for the code. 
		 * If no code is given then the first code available will be used. Returns an error string.
		 * @param $code string
		 * @param $messages instance of WP_Error class
		 * @return string or null
		 */
		function message($code='', $messages) {
			return $messages->get_error_message($code);
		}
		
	}
	
}

?>