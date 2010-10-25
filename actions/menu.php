<?php
if ( !class_exists('SiteUpgradeMenuActions') ) {
    class SiteUpgradeMenuActions extends SiteUpgradeAction {
        var $functions = array('menu_exists', 'menu_not_exists', 'menu_create', 'menu_update');
        public function menu_exists($slug) {
            return is_nav_menu(current($slug));
        }
        public function menu_not_exists($slug) {
            return !$this->menu_exists($slug);
        }
        public function menu_create($args) {
        }
        /**
         * deletes the menu with given slug
         * creates a new menu
         * add menu items to it
         * set its location
         * 
         * @param  $menu
         * @return WP_Error
         */
        public function menu_update($menu) {

            if (!array_key_exists('slug', $menu)) {
                return new WP_Error('error', __('slug is not specified'));
            }
            if (is_nav_menu($menu['slug'])) // navigation menu exists 
            {
                $new_menu = array(
                    'description' => $menu['description'],
                    'menu-name'   => $menu['name'],
                    'parent'      => $menu['parent'],
                    'slug'        => $menu['slug'],
                );
                $locations = get_theme_mod( 'nav_menu_locations');
                if (wp_delete_nav_menu($menu['slug'])) // delete nav menu
                {
                    $menu_id = wp_update_nav_menu_object(0, $new_menu);// create_nav_menu() uses only 'menu-name', therefore we have to use this
                    foreach ($menu['nav_menu_locations'] as $index => $menu_location)
                    {
                        $locations[$menu_location] = $menu_id;// set this nav menu to all the locations
                    }
                    set_theme_mod( 'nav_menu_locations', $locations);

                    if (is_numeric($menu_id)) // successfully created
                    {
                        $menu_items = $menu['menu_items'];

                        foreach ($menu_items as $menu_item)
                        {
                            $parent_id = 0;
                            if ($menu_item['menu_item_parent'] != '0')
                            {
                                $parent = $this -> get_object($menu_item['menu_item_parent'], $menu_item['menu_item_parent_type']);
                                $parent_nav_menu_item = wp_get_associated_nav_menu_items($parent->ID, $this -> get_type ($menu_item['menu_item_parent_type']));
                                $parent_id = $parent_nav_menu_item[0];
                            }
                            if ($menu_item['object'] == 'custom')
                            {
                                $menu_item_data = $this -> get_menu_item_data(null, $parent_id, $menu_item);
                            }
                            else 
                            {
                                $object = $this -> get_object($menu_item['slug'], $menu_item['object']);
                                $menu_item_data = $this -> get_menu_item_data($object->ID, $parent_id, $menu_item);
                            }
                            $menu_item_db_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
                        }
                    }
                }
            }
        }
        /**
         * generates menu create/update scripts
         * 
         * @param  $code
         * @return
         */
        public function generate($code) {
            if (array_key_exists('update-menus', $_POST) && $menus = $_POST['update-menus']) {
                $code .= $this->menus_code ('update-menus', $menus);
            }
            if (array_key_exists('create-menus', $_POST) && $menus = $_POST['create-menus']) {
                $code .= $this->menus_code ('create-menus', $menus);
            }
            return $code;
        }
        /**
         * Sets menu item keys to be exported to the upgrade
         * @param  $menu_item
         * @return
         */
        protected function get_menu_items_export($menu_item, $menu_item_parent) {
            $object = $this -> get_original_object($menu_item->ID);
            if ($menu_item_parent != 0)
                $parent = $this -> get_original_object($menu_item_parent->ID);
            else $parent->slug = 0;

            $menu_items_export = array(
                'post_title'        => $menu_item->post_title,// for links only
                'slug'              => $object->slug,   // this is enough to identify a post/category on the production site
                'menu_order'        => $menu_item->menu_order,
                'post_type'         => $menu_item->post_type,
                'menu_item_parent'  => $parent->slug,
                'menu_item_parent_type'  => $parent->menu_item_parent_type,
                'object'            => $menu_item->object,
                'type'              => $menu_item->type,
                'type_label'        => $menu_item->type_label,
                'url'               => $menu_item->url, // used when menu-item is a link. In wp_postmeta
                'title'             => $menu_item->title, // used when menu-item is a link
                'target'            => $menu_item->target,
                'attr_title'        => $menu_item->attr_title,
                'classes'           => implode(' ', $menu_item->classes),
                'xfn'               => $menu_item->xfn,
                'post_status'       => $menu_item->post_status,
             );

            return $menu_items_export;
        }
        /**
         * Generates HTML code
         * @param  $type
         * @param  $menu_slugs
         * @return string
         */
        protected function menus_code($type, $menu_slugs) {

            switch ($type):
                case 'update-menus':
                    $this->h2o->loadTemplate('menu-update.code');
                    break;
                case 'create-menus':
                    $this->h2o->loadTemplate('menu-create.code');
                    break;
                default:
                    return '';
            endswitch;
            
            $code = '';

            // foreach menu
            foreach ($menu_slugs as $menu_slug )
            {
                $menu_obj   = wp_get_nav_menu_object($menu_slug);
                $locations  = get_nav_menu_locations();
                $menu_obj->nav_menu_locations = array_keys($locations);// TODO what if this menu is being displayed more than one place

                $menu_items = wp_get_nav_menu_items ($menu_slug);

                foreach ($menu_items as $mi)
                {
                    $menu_item_parent = $this -> get_nav_menu_item($menu_items, $mi->menu_item_parent);
                    $menu_obj->menu_items[] = $this -> get_menu_items_export($mi, $menu_item_parent);
                }
                $value = $this->serialize($menu_obj);
                $code .= $this->h2o->render(array('slug'=>$menu_slug, 'slug_array'=>$this->serialize($menu_slug), 'value'=>$value));
            }
            return $code;
        }
        /**
         * Prepares HTML to be displayed on Create Upgrade admin page
         * @param  $title
         * @return
         */
        protected function get_checkbox_list($title) {
            switch ($title):
                case 'Create Menus':
                    $this->h2o->loadTemplate('menus-create.html');
                break;
                case 'Update Menus':
                    $this->h2o->loadTemplate('menus-update.html');
                break;
            endswitch;

            $menus = wp_get_nav_menus();
            return $this->h2o->render(array('menus'=>$menus, 'title'=>$title));
        }
        /**
         * Shows lists of menus in admin for the generation of create/update script
         * @param  $elements
         * @return
         */
        public function admin($elements) {
            $elements[__('Menus')] = $this->get_checkbox_list('Create Menus') . $this->get_checkbox_list('Update Menus') ;
            return $elements;
        }
        /**
         * returns the original object of type = $type and slug = $slug
         * used while executing menu upgrade scripts
         * @param  $slug
         * @param  $type
         * @return Category|mixed|object
         */
        protected function get_object ($slug, $type) {
            switch ($type) :
                case 'category':
                    $cat = get_category_by_slug($slug);
                    $cat->ID = $cat->term_id;// to make handling consistent in the caller function
                    return $cat;
                case 'page':
                    return get_post($this->get_id_by_post_name($slug));
                case 'custom':
                    return get_post($this->get_id_by_post_name($slug));// for custom it will work only for parent becuase it would have been created already
            endswitch;
        }
        /**
         * returns the original object being referred to by a nav menu item with id = $ID
         * used while generating menu upgrade scripts
         * @param  $ID
         * @return mixed
         */
        protected function get_original_object ($ID) {
            $object    = get_post_meta($ID, '_menu_item_object'   , true);
            $object_id = get_post_meta($ID, '_menu_item_object_id', true);
            switch($object):
                case 'category':
                    $cat = get_category($object_id);
                    $cat->menu_item_parent_type = 'category';
                    return $cat;
                case 'page':
                    $post = get_post($object_id);
                    $post->slug = $post->post_name; // to make handling consistent in the caller function
                    $post->menu_item_parent_type = 'page';
                    return $post;
                case 'custom':
                    $post = get_post($object_id); // $object_id == $ID
                    $post->slug = $post->post_name;
                    $post->menu_item_parent_type = 'custom';
                    return $post;
                default:
                    return null;
            endswitch;
        }
        /**
         * finds a nav menu item of given $ID in $menu_items and returns the object 
         * @param  $ID
         * @param  $menu_items
         * @return string
         */
        protected function get_nav_menu_item($menu_items, $ID) {
            if ($ID == 0) return 0;
            foreach ($menu_items as $mi) {
                if ($mi->ID == $ID) return $mi; 
            }
            return 0;// ideally this should not execute ever
        }
        /**
         * returns type based on object (type)
         * @param  $object
         * @return string
         */
        protected function get_type($object) {
            switch ($object):
                case 'page':
                    return 'post_type';
                case 'category':
                    return 'taxonomy';
                case 'custom':
                    return 'custom';
                default:
                    return null;
            endswitch;
        }
        /**
         * prepares and returns an array to create a nav menu item
         * @param  $object_id
         * @param  $parent_id
         * @param  $menu_item
         * @return
         */
        protected function get_menu_item_data($object_id, $parent_id, $menu_item) {
            $menu_item_data = array(
                'menu-item-db-id' => 0,
                'menu-item-object-id' => $object_id,// the id if the original item being referred to
                'menu-item-object' => $menu_item['object'],
                'menu-item-parent-id' => $parent_id,
                'menu-item-position' => $menu_item['menu_order'],
                'menu-item-type' => $menu_item['type'],
                'menu-item-title' => $menu_item['title'],
                'menu-item-url' => $menu_item['url'],// TODO this is not correct while permalink options is changed from default
                'menu-item-description' => $menu_item['description'],
                'menu-item-attr-title' => $menu_item['attr_title'],
                'menu-item-target' => $menu_item['target'],
                'menu-item-classes' => $menu_item['classes'],
                'menu-item-xfn' => $menu_item['xfn'],
                'menu-item-status' => $menu_item['post_status'],
            );
            return $menu_item_data;
        }
    }
    new SiteUpgradeMenuActions();
}
?>