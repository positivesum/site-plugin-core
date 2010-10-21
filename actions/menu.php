<?php
if ( !class_exists('SiteUpgradeMenuActions') ) {
    class SiteUpgradeMenuActions extends SiteUpgradeAction {
        var $functions = array('menu_exists', 'menu_not_exists', 'menu_create', 'menu_update', 'menu_delete');
        function menu_exists($slug) {
            return is_nav_menu(current($slug));
        }
        function menu_not_exists($slug) {
            return !$this->menu_exists($slug);
        }
        function menu_create($args) {
        }
        /**
         * deletes the menu with given slug
         * creates a new menu
         * add menu items to it
         * set its location
         * 
         * @param  $args
         * @return WP_Error
         */
        function menu_update($args) {

            if (!array_key_exists('slug', $args)) {
                return new WP_Error('error', __('slug is not specified'));
            }
            if (is_nav_menu($args['slug'])) {// navigation menu exists
//                $menu_object = wp_get_nav_menu_object($args['slug']);
                $new_menu = array(
                    'description' => $args['description'],
                    'menu-name'   => $args['name'],
                    'parent'      => $args['parent'],
                    'slug'        => $args['slug'],
                );
                $locations = get_theme_mod( 'nav_menu_locations');
                if (wp_delete_nav_menu($args['slug'])) {// delete nav menu
                    $menu_id = wp_update_nav_menu_object(0, $new_menu);// create_nav_menu() uses only 'menu-name', therefore we have to use this
                    foreach ($args['nav_menu_locations'] as $index => $menu_location) {
                        $locations[$menu_location] = $menu_id;// set this nav menu to all the locations
                    }
                    set_theme_mod( 'nav_menu_locations', $locations);

                    if (is_numeric($menu_id)) {// successfully created
                        $menu_items = $args['menu_items'];

                        foreach ($menu_items as $menu_item) {
                            switch ($menu_item['object']):
                                case 'page':
                                    $nav_menu_item_post_id = $this->nav_menu_item_post_id($menu_item['slug']);// If nothing is found, this item will be added. The previous one will not be removed
                                    $post = get_post($this->get_id_by_post_name($menu_item['slug']));
                                    $menu_item_data = array(
                                        'menu-item-db-id' => $nav_menu_item_post_id,
                                        'menu-item-object-id' => $post->ID,// the id if the original item being referred to
                                        'menu-item-object' => $menu_item['object'],
                                        'menu-item-parent-id' => $menu_item['menu_item_parent'],// TODO this is not correct
                                        'menu-item-position' => $menu_item['menu_order'],
                                        'menu-item-type' => $menu_item['type'],
                                        'menu-item-title' => $menu_item['title'],
                                        'menu-item-url' => $menu_item['url'],// TODO this is not correct
                                        'menu-item-description' => $menu_item['description'],
                                        'menu-item-attr-title' => $menu_item['attr_title'],
                                        'menu-item-target' => $menu_item['target'],
                                        'menu-item-classes' => $menu_item['classes'],
                                        'menu-item-xfn' => $menu_item['xfn'],
                                        'menu-item-status' => $menu_item['post_status'],
                                    );
                                    $menu_item_db_id = wp_update_nav_menu_item($menu_id, $nav_menu_item_post_id, $menu_item_data);
                                    break;
                                case 'category':
                                    $cat = get_category_by_slug($menu_item['slug']);
                                    // assumption: the category upgrade script has already been executed and all categories exist
                                    // nav menu item for the parent category has already been created
                                    // create nav menu item for current entry
                                    if ($menu_item['menu_item_parent']) {
                                        $cat_parent = get_category_by_slug($menu_item['menu_item_parent']);
                                        $id = $cat_parent->term_id;// or cat_ID
                                        $ass_nav_menu_items = wp_get_associated_nav_menu_items($id, 'taxonomy');
                                        $menu_item['menu_item_parent'] = $ass_nav_menu_items[0]; // TODO what if more than one associated nav menu items are found
                                    }
                                    // else this menu item has no parent, create it directly
                                    $menu_item_data = array(
                                        'menu-item-db-id' => $nav_menu_item_post_id,
                                        'menu-item-object-id' => $cat->term_id,// the id if the original item being referred to
                                        'menu-item-object' => $menu_item['object'],
                                        'menu-item-parent-id' => $menu_item['menu_item_parent'],
                                        'menu-item-position' => $menu_item['menu_order'],
                                        'menu-item-type' => $menu_item['type'],
                                        'menu-item-title' => $menu_item['title'],
                                        'menu-item-url' => $menu_item['url'],// TODO this is not correct
                                        'menu-item-description' => $menu_item['description'],
                                        'menu-item-attr-title' => $menu_item['attr_title'],
                                        'menu-item-target' => $menu_item['target'],
                                        'menu-item-classes' => $menu_item['classes'],
                                        'menu-item-xfn' => $menu_item['xfn'],
                                        'menu-item-status' => $menu_item['post_status'],
                                    );
                                    wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
                                    break;
                                case 'custom':
                                    $menu_item_data = array(
                                        'menu-item-db-id' => null,
                                        'menu-item-object-id' => null,// the id if the original item being referred to
                                        'menu-item-object' => $menu_item['object'],
                                        'menu-item-parent-id' => $this->get_id_by_post_name($menu_item['menu_item_parent']),
                                        'menu-item-position' => $menu_item['menu_order'],
                                        'menu-item-type' => $menu_item['type'],
                                        'menu-item-title' => $menu_item['title'],
                                        'menu-item-url' => $menu_item['url'],// TODO this is not correct
                                        'menu-item-description' => $menu_item['description'],
                                        'menu-item-attr-title' => $menu_item['attr_title'],
                                        'menu-item-target' => $menu_item['target'],
                                        'menu-item-classes' => $menu_item['classes'],
                                        'menu-item-xfn' => $menu_item['xfn'],
                                        'menu-item-status' => $menu_item['post_status'],
                                    );
                                    $menu_item_db_id = wp_update_nav_menu_item($menu_id, $nav_menu_item_post_id, $menu_item_data);
                                    break;
                                default:
                            endswitch;
                        }
                    }
                }
            }
        }
        function menu_item_create() {

        }
        function menu_item_update() {
            
        }
        function menu_delete() {
            
        }
        /**
         * generates menu create/update scripts
         * 
         * @param  $code
         * @return
         */
        function generate($code) {
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
        protected function get_menu_items_export($menu_item) {
            switch ($menu_item->object):
                case 'category':
                    $category = get_category($menu_item->object_id);
                    $slug = $category->slug;
                    if ($menu_item->post_parent != 0) {
                        $parent_category = get_category($menu_item->post_parent);
                        $menu_item->post_parent = $parent_category->slug;
                    }
                    break;
                case 'page':
                    $post = get_post($menu_item->object_id);
                    $slug = $post->post_name;
                    break;
                case 'custom':
                    $slug = $menu_item->post_name;
                    if ($menu_item->menu_item_parent != '0') {
                        $parent_custom = get_post($menu_item->menu_item_parent);
                        $menu_item->post_parent = $parent_custom->post_name;
                    }
//                    $menu_item->post_parent = get_post();
                    break;
                default:
            endswitch;
            $menu_items_export = array(
                'post_title'        => $menu_item->post_title,// for links only
                'slug'              => $slug,   // this is enough to identify a post/category on the production site
                'menu_order'        => $menu_item->menu_order,
                'post_type'         => $menu_item->post_type,
                'menu_item_parent'  => $menu_item->post_parent,
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
            $code = '';
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
            
            // foreach menu
            foreach ($menu_slugs as $menu_slug ) {
                $menu_obj   = wp_get_nav_menu_object($menu_slug);
                $locations = get_nav_menu_locations();
                $menu_obj->nav_menu_locations = array_keys($locations);// TODO what if this menu is being displayed more than one place

                $menu_items = wp_get_nav_menu_items ($menu_slug);
                // foreach menu item in menu
                foreach ($menu_items as $menu_item) {
                    $menu_obj->menu_items[] = $this->get_menu_items_export($menu_item);
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
        function admin($elements) {
            $elements[__('Menus')] = $this->get_checkbox_list('Create Menus') . $this->get_checkbox_list('Update Menus') ;
            return $elements;
        }
        /**
         * Returns db_id of menu item using post_name of the actual object (page) being referred to
         * @param  $post_name
         * @return null|string
         */
        function nav_menu_item_post_id($post_name)
        {
            global $wpdb;
            $id = $wpdb->get_var("SELECT MAX( b.post_id )
                FROM $wpdb->posts a, $wpdb->postmeta b
                WHERE a.id = b.meta_value
                AND b.meta_key =  '_menu_item_object_id'
                AND a.post_name = '".$post_name."'");
            return $id;
        }
    }
    new SiteUpgradeMenuActions();
}
?>