<?php 

if ( !class_exists('SiteUpgradeThemeActions') ) {
	
	class SiteUpgradeThemeActions extends SiteUpgradeAction {
		
		var $functions = array('theme_exists', 'switch_theme');
	
		 /*
		  * Switches current theme to new template and stylesheet names.
		  * http://codex.wordpress.org/Function_Reference/switch_theme#Examples
		  * @param str $template
		  * @param str $stylesheet
		  */
		  function switch_theme($template, $stylesheet) {
		  	  switch_theme($template, $stylesheet);
		  }
		  
		 /*
		  * Verify that specified theme is exists
		  * @param str $theme
		  * @return boolean
		  */
		  function theme_exists($theme) {
		  
		  	  return !is_null(get_theme($theme));
		  	  
		  }
		  
		  
		  function admin( $elements ) {
		  	  
		  	  $themes = get_themes();
		  	  
		  	  $this->h2o->loadTemplate('themes.html');
		  	  
		  	  $elements[__('Theme')] = $this->h2o->render(array('themes'=>$themes));
		  	  
		  	  return $elements;
		  
		  }
				
		/*
		 * This method is called when upgrade script for an action is being generated.
		 * @param $args necessary for function's operation
		 * @return str of php code to add to upgrade script
		 */
		function generate($code) {
			
			$result = array();
			if ( array_key_exists('theme', $_POST) && $_POST['theme'] ) $theme = $_POST['theme'];
			$theme = get_theme($theme);

			if ( !is_null($theme) ) {
				$this->h2o->loadTemplate('themes.code');
				$value = Spyc::YAMLDump(array('template'=>$theme['Template'], 'stylesheet'=>$theme['Stylesheet']));
				$code .= $this->h2o->render(array('name'=>$theme['Name'], 'value'=>$value));
			}
			
			return $code;
		}
		
	}

	new SiteUpgradeThemeActions();
	
}
