<?php 

if ( !class_exists('SiteUpgradePageActions') ) {
	
	class SiteUpgradePageActions extends SiteUpgradeAction {
		
		var $functions = array('page_update', 'page_exists', 'page_create');
		 
		 /*
		  * Check if page exists
		  * @param str $slug
		  * @return boolean
		 */
		 function page_exists( $slug ) {
		 	 return is_page( $slug );
		 }

		 /*
		  * Check if page does not exist
		  * @param str $slug
		  * @return boolean
		 */
		 function page_not_exists( $slug ) {
		 	 return !is_page( $slug );
		 }		 

		/*
		 * Updates page to values specified in array
		 * @param array $args
		 * @return true|WP_Error
		*/
		function category_update($args) {
		 
			if ( !array_key_exists('id', $args) ) {
			 return new WP_Error('error', __('Page id is not specified'));
			}
			
			$data = array();
			# TODO: load values for page update
			
			if ( is_wp_error($result) ) return new WP_Error('error', __('Error occured while updating page'));
			else return true;
		 
		}
		 
		function get_checklist($title) {
			require_once(ABSPATH . 'wp-admin/includes/meta-boxes.php');
			
			$html = array();
			$name = sanitize_title_with_dashes($title);
			$html[] = '<h5>'.__($title).'</h5>';
			$html[] = '<ul id="'.$name.'" class="list:category categorychecklist form-no-clear">';
			ob_start();
			$page = get_page($id=0);
			$args = array( 'taxonomy' => 'category', 'popular_cats' => wp_popular_terms_checklist('category'));
			wp_terms_checklist($page->ID, $args);
			$checklist = ob_get_contents();
			$html[] = str_replace('page_category', $name, $checklist);
			ob_end_clean();
			$html[] = '</ul>';		 	 
			
			return $html;
		}
		 
		function admin( $elements ) {
		 
		 $html = array_merge($this->get_checklist('Create Categories'), $this->get_checklist('Update Categories'));		 	 
		 $html[] = '<style type="text/css">ul.children { margin-left: 10px; }</style>';
		 $elements[__('Categories')] = implode("\n", $html);
		
		 return $elements;
		
		}
		 
		/*
		 * Return string of code to output for upgrade file
		 * @param str type ( `update-categories` or `create-categories` )
		 * @param array $cat_ids categories to include
		 * @return str of code
		*/
		function categories_code($type, $cat_ids) {
			
			$code = '';
			
			switch ( $type ) :
				case ( 'update-categories' ) : $this->h2o->loadTemplate('category-update.code'); break;
				case ( 'create-categories' ) : $this->h2o->loadTemplate('category-create.code'); break;
				default: return '';
			endswitch;
				
			foreach ( $cat_ids as $cat_id ) {
				$c = get_category($cat_id, ARRAY_A);
				$data = array(
					'id'=>$c['cat_ID'],
					'name'=>$c['name'], 'description'=>$c['description'], 
					'parent'=>$c['parent'], 'slug'=>$c['slug']
					);
				$value = Spyc::YAMLDump($data);
				$code .= $this->h2o->render(array('id'=>$c['cat_ID'], 'name'=>$c['name'], 'value'=>$value, 'slug'=>$c['slug']));					
			}
			
			return $code;
		}
		
		/*
		 * This method is called when upgrade script for an action is being generated.
		 * @param $args necessary for function's operation
		 * @return str of php code to add to upgrade script
		 */
		function generate($code) {
			
			$result = array();
		
			if ( array_key_exists('create-categories', $_POST) && $cat_ids = $_POST['create-categories']) 
				$code .= $this->categories_code('create-categories', $cat_ids);
			
			if ( array_key_exists('update-categories', $_POST) && $cat_ids = $_POST['update-categories']) 
				$code .= $this->categories_code('update-categories', $cat_ids);
			
			return $code;
			
		}
		
	}

	new SiteUpgradePageActions();
	
}

?>
