<?php

if ( !class_exists('SiteUpgradePageActions') ) {

	class SiteUpgradePageActions extends SiteUpgradeAction {

		var $functions = array('page_update', 'page_exists', 'page_not_exists', 'page_create', 'page_update_meta');

		 /*
		  * Check if page exists
		  * @param str $slug
		  * @return boolean
		 */
		 function page_exists( $post_name ) {
             $id = $this->get_id_by_post_name(current($post_name));
		 	 return (boolean) get_page( ($id), ARRAY_A );
		 }

		 /*
		  * Check if page does not exist
		  * @param str $slug
		  * @return boolean
		 */
		 function page_not_exists( $post_name ) {
		 	 return !$this->page_exists($post_name);
		 }
        /**
         * This function updates postmeta of a page
         * @param  $args
         * @return void
         */
        function page_update_meta($args) {
            if ( !array_key_exists('post_name', $args) ) {
                return new WP_Error('error', __('post_name is not specified'));
            }

            $post_id = $this->get_id_by_post_name($args['post_name']);
            unset($args['post_name']);// that is not a valid custom field, It was exported just to identify a post

            // delete previous meta fields on production site
            if ($keys = get_post_custom_keys($post_id)) {
                foreach ($keys as $key) {
                    delete_post_meta($post_id, $key);
                }
            }
            // add new meta fields on production site
            foreach($args as $meta_key => $meta_values) {
                foreach ($meta_values as $meta_value) {
                    add_post_meta($post_id, $meta_key, $meta_value);
                }
            }
        }
        /*
		 * Updates page to values specified in array
		 * @param array $args
		 * @return true|WP_Error
		*/
        function page_update($args) {

            if ( !array_key_exists('post_name', $args) ) {
                return new WP_Error('error', __('post_name is not specified'));
            }

            if (wp_update_post($args) === 0) {
                return new WP_Error('error', __('Error occured while updating page'));
            }
            return true;
        }
       /**
        * @param  $args
        * @return bool|WP_Error
        */
        function page_create($args) {

            if (!array_key_exists('post_name', $args)) {
                return new WP_Error('error', __('post_name is not specified'));
            }
            unset($args['ID']); // to avoid updating a post with the given ID

            $page_id = $this->get_id_by_post_name($args['post_name']);

            $prev_page = get_page( $page_id, ARRAY_A );

            if ($prev_page && $prev_page['post_status'] === 'trash')
            {
                wp_delete_post($page_id, true);// if a post already exists in trash then post_name will identify that post and post we created will be assigned a new post name
            }
            else if ($prev_page)
            {
                return new WP_Error('error', __('A page with the current post_name already exists. Please generate update script.'));
            }

            $id = wp_insert_post($args);
            if ($id === 0 OR ($id instanceof WP_Error)) {
                return new WP_Error('error', __('Error occured while creating page'));
            } else {
               wp_publish_post( $id );
            }
            return true;
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
                $post_name_array  = $this->serialize($p['post_name']);
				$code .= $this->h2o->render(array( 'post_name' => $p['post_name'], 'post_name_array'=>$post_name_array, 'value'=>$value));
                $code .= $this->pages_meta_code($page_id, $p['post_name']);
			}

			return $code;
		}
        /**
         * This method generates script for meta data of a page
         * @param  $page_id
         * @return string
         */
        function pages_meta_code($page_id, $post_name) {
            $code = '';
            $this->h2o->loadTemplate('page-update-meta.code');
            $custom_vars = get_post_custom($page_id);
            // TODO exclude fields starting from '_'
            $custom_vars['post_name'] = $post_name; // adding post_name
            $custom_vars = $this->serialize($custom_vars);
            $post_name_array = $this->serialize($post_name);
            $code = $this->h2o->render(array('post_name'=>$post_name, 'value'=>$custom_vars, 'post_name_array'=>$post_name_array));
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

			$code =  htmlspecialchars_decode($code, ENT_QUOTES);// TODO only decode the code generated by $this->generate
            return $code;

		}
        /**
         * Return ID using $post_name
         * @param  $post_name
         * @return null|string
         */
        function get_id_by_post_name($post_name)
        {
            global $wpdb;
            $id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$post_name'");
            return $id;
        }
	}

	new SiteUpgradePageActions();

}

?>
