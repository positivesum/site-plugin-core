<?php

if ( !class_exists('SiteUpgradePageActions') ) {

	class SiteUpgradePageActions extends SiteUpgradeAction {

		var $functions = array('page_update', 'page_exists', 'page_not_exists', 'page_create', 'page_update_meta');

		 /*
		  * Check if page exists
		  * @param str $slug
		  * @return boolean
		 */
		 function page_exists( $ID ) {
		 	 return (boolean) get_page( current($ID), ARRAY_A );
		 }

		 /*
		  * Check if page does not exist
		  * @param str $slug
		  * @return boolean
		 */
		 function page_not_exists( $ID ) {
		 	 return !get_page( current($ID), ARRAY_A );
		 }
        /**
         * This function updates postmeta of a page
         * @param  $args
         * @return void
         */
        function page_update_meta($args) {
            if ( !array_key_exists('ID', $args) ) {
                return new WP_Error('error', __('Page id is not specified'));
            }
            $post_id = $args['ID'];
            unset($args['ID']);
            foreach($args as $meta_key => $meta_values) {
                foreach ($meta_values as $meta_value) {
                    update_post_meta($post_id, $meta_key, $meta_value);
                }
            }
        }
		/*
		 * Updates page to values specified in array
		 * @param array $args
		 * @return true|WP_Error
		*/
        function page_update($args) {

            if ( !array_key_exists('ID', $args) ) {
                return new WP_Error('error', __('Page id is not specified'));
            }

            if (wp_update_post($args) === 0) {
                return new WP_Error('error', __('Error occured while updating page'));
            }
            return true;
        }
        /**
         * @param  $args
         * @return WP_Error
         */
        function page_create($args) {
            $args['ID'] = null;
            $id = wp_insert_post($args);
            if ($id === 0) {
                return new WP_Error('error', __('Error occured while updating page'));
            } else {
               wp_publish_post( $id );
            }
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

		/**
         * @param  $type
         * @param  $page_ids
         * @return string
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
                $slug  = $this->serialize($p['ID']);
				$code .= $this->h2o->render(array( 'ID' => $p['ID'], 'name'=>$p['post_title'], 'slug'=>$slug, 'value'=>$value));
                $code .= $this->pages_meta_code($page_id);
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

			$code =  htmlspecialchars_decode($code, ENT_QUOTES);
            return $code;

		}
        /**
         * This method generates script for postmeta of a page
         * @param  $page_id
         * @return string
         */
        function pages_meta_code($page_id) {
            $code = '';
            $this->h2o->loadTemplate('page-update-meta.code');
            $custom_vars = get_post_custom($page_id);
            $custom_vars['ID'] = $page_id; // adding page ID
            $custom_vars = $this->serialize($custom_vars);
            $slug = $this->serialize($page_id);
            $code = $this->h2o->render(array('ID'=>$page_id, 'value'=>$custom_vars, 'slug'=>$slug));
            return $code;
        }
	}

	new SiteUpgradePageActions();

}

?>
