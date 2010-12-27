<?php 

if ( !class_exists('SiteUpgradeCategoryActions') ) {
	
	class SiteUpgradeCategoryActions extends SiteUpgradeAction {
		
		var $functions = array('category_update', 'category_exists', 'category_create', 'category_not_exists');
	
        /**
         * @static
         * @param  $args
         * @return void
         */
        public static function category_create($args) {
            if ( !array_key_exists('name', $args)) {
                 return new WP_Error('error', __('Category name is not specified'));
            }
            $parent = get_category_by_slug($args['parent']);
            $args['parent'] = $parent->cat_ID;
            $result = wp_insert_term($args['name'], 'category', $args);
             if ( is_wp_error($result) )
                 return new WP_Error('error', __('Error occured while trying to insert category'));
		 	 else
                  return true;
        }
        /*
		 * Updates category to values specified in array
		 * @param array $args
		 * @return true|WP_Error
		 */
		 public static function category_update($args) {
		 
		 	 if ( !array_key_exists('cat_ID', $args) ) {
		 	 	 return new WP_Error('error', __('Category id is not specified'));
		 	 }
		 	 
		 	 unset($args['cat_ID']);
             $category = get_category_by_slug($args['slug']);
             $parent = get_category_by_slug($args['parent']);
		 	 
		 	 $data = array();
		 	 if ( array_key_exists('name', $args) ) $data['name'] = $args['name'];		 	 
		 	 if ( array_key_exists('description', $args) ) $data['description'] = $args['description'];
		 	 if ( array_key_exists('parent', $args) ) $data['parent'] = $parent -> cat_ID;
		 	 if ( array_key_exists('slug', $args) ) $data['slug'] = $args['slug'];
		 	 
		 	 $result = wp_update_term( $category->term_id, 'category', $data);
		 	 
		 	 if ( is_wp_error($result) ) return new WP_Error('error', __('Error occured while trying to update category'));
		 	 else return true;
		 
		 }
		 
         /**
          * Returns true if category exists so that it can be updated
          * @static
          * @param  $arg
          * @return bool
          */
		 public static function category_exists( $arg ) {
		 	 return (boolean) get_category_by_slug( current($arg));
		 }

        /**
         * Returns false if category exists so that the attempt to create category fails
         * @static
         * @param  $arg
         * @return bool
         */
		 public static function category_not_exists( $arg ) {
		 	 return !get_category_by_slug( current($arg));
		 }		 
		 
		 function get_checklist($title) {
		 	 require_once(ABSPATH . 'wp-admin/includes/meta-boxes.php');
		 	 
		 	 $html = array();
		 	 $name = sanitize_title_with_dashes($title);
		 	 $html[] = '<h5>'.__($title).'</h5>';
		 	 $html[] = '<ul id="'.$name.'" class="list:category categorychecklist form-no-clear">';
		 	 ob_start();
		 	 $post = get_post($id=0);
		 	 $args = array( 'taxonomy' => 'category', 'popular_cats' => wp_popular_terms_checklist('category', 0, 0, false));
			 wp_terms_checklist($post->ID, $args);
			 $checklist = ob_get_contents();
			 $html[] = str_replace('post_category', $name, $checklist);
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
				$category = get_category($cat_id, ARRAY_A);
                if ($category['parent'] != '0')
                    $parent = get_category($category['parent'], ARRAY_A);
                else
                    $parent['slug'] = '0';
				$data = array(
					'cat_ID'=>$category['cat_ID'],
					'name'=>$category['name'], 'description'=>$category['description'],
					'parent'=>$parent['slug'], 'slug'=>$category['slug']
					);
				$value = $this->serialize($data);
                $id = $this->serialize(array($category['cat_ID']));
                $slug = $this->serialize(array($category['slug']));
				$code .= $this->h2o->render(array('id'=>$id, 'name'=>$category['name'], 'value'=>$value, 'slug'=>$slug));
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

	new SiteUpgradeCategoryActions();

}