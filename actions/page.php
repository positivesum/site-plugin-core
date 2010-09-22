<?php

if ( !class_exists('SiteUpgradePageActions') ) {

	class SiteUpgradePageActions extends SiteUpgradeAction {

		var $functions = array('page_update', 'page_exists', 'page_not_exists', 'page_create');

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
		function page_update($args) {

			if ( !array_key_exists('id', $args) ) {
			 return new WP_Error('error', __('Page id is not specified'));
			}

			$data = array();
			# TODO: load values for page update

			if ( is_wp_error($result) ) return new WP_Error('error', __('Error occured while updating page'));
			else return true;

		}
        /**
         * Creates a page 
         * @param  $args
         * @return void
         */
        function page_create($args) {

        }
		/**
         * Creates the checklist of pages to be created and updated
         * @param  $title
         * @return 
         */
        function get_checklist($title) {
            switch ($title):
                case 'Create Pages':
                    $this->h2o->loadTemplate('pages-create.html');
                break;
                case 'Update Pages':
                    $this->h2o->loadTemplate('pages-update.html');
                break;
            endswitch;

            $pages = get_pages();
            return $this->h2o->render(array('pages'=>$pages, 'title'=>$title));
		}

        function admin( $elements ) {
            $elements[__('Pages')] = $this->get_checklist('Create Pages') . $this->get_checklist('Update Pages') ;
            return $elements;
        }

		/*
		 * Return string of code to output for upgrade file
		 * @param str type ( `update-categories` or `create-categories` )
		 * @param array $cat_ids categories to include
		 * @return str of code
		*/
		function pages_code($type, $page_ids) {

			$code = '';

			switch ( $type ) :
				case ( 'create-pages' ) : $this->h2o->loadTemplate('page-create.code'); break;
				case ( 'update-pages' ) : $this->h2o->loadTemplate('page-update.code'); break;
				default: return '';
			endswitch;

			foreach ( $page_ids as $page_id ) {
				$p = get_page($page_id, ARRAY_A);
				$value = $this->serialize($p);
                $slug = $this->serialize(array($p['post_name']));
				$code .= $this->h2o->render(array( 'name'=>$p['post_title'], 'slug'=>$slug, 'value'=>$value));
			}

			return $code;
		}

		/*
		 * This method is called when upgrade script for an action is being generated.
		 * @param $args necessary for function's operation
		 * @return str of php code to add to upgrade script
		 */
		function generate($code) {

			if ( array_key_exists('create-pages', $_POST) && $page_ids = $_POST['create-pages'])
				$code .= $this->pages_code('create-pages', $page_ids);

            if ( array_key_exists('update-pages', $_POST) && $page_ids = $_POST['update-pages'])
				$code .= $this->pages_code('update-pages', $page_ids);

			return $code;

		}

	}

	new SiteUpgradePageActions();

}

?>
