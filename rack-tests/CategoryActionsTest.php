<?php

class CategoryActionsTest extends PhpRack_Test {
	
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

   public function getLabel() {
        return 'Category Actions Test'; // Test Label
    }	
	
	public function testÑategoryExists() {
        if (SiteUpgradeCategoryActions::category_exists(array('products'))) {
            $this->_log("Products category exists!");
        } else {
            $this->assert->fail("Test Ñategory Exists just failed");		
        }
    }	

/*
	function testCategoryExistsError() {
        if (is_wp_error(SiteUpgradeCategoryActions::category_exists(array('rockets')))) {
            $this->_log("Rockets category exists!");
        } else {
            $this->assert->fail("Test Category Exists Error just failed");		
        }	
	}
*/	
	function testCategoryNotExists() {
        if (SiteUpgradeCategoryActions::category_not_exists(array('rockets'))) {
            $this->_log("Rockets category not exists!");
        } else {
            $this->assert->fail("Test Category Exists Error just failed");		
        }	
	}
	
}

?>
