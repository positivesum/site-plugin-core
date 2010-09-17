<?php

class SP_CategoryActionsTest extends WPTestCase {
	
	function setUp() {
		parent::setUp();
		$args = array(
			'cat_name' => 'Products',
			'category_description' => 'Products go into this category.',
			'category_nicename' => 'products',
			'category_parent' => 0
			);

		$this->cat_ID = wp_insert_category($args);
	}
	
	function tearDown() {
		parent::tearDown();
		wp_delete_category($this->cat_ID);
	}
	
	function test_category_exists() {
		$this->assertTrue(SiteUpgradeCategoryActions::category_exists('products'));
	}

	function test_category_exists_error() {
		$this->assertTrue(is_wp_error(SiteUpgradeCategoryActions::category_exists('rockets')));
	}
	
	function test_category_not_exists() {
	
		$this->assertTrue(SiteUpgradeCategoryActions::category_not_exists('rockets'));
		$this->assertFalse(SiteUpgradeCategoryActions::category_not_exists('products'));
		
	}
	
	
	
}

?>
