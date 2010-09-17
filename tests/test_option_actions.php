<?php

class SP_OptionActionsTest extends WPTestCase {
	
	function setUp() {
		parent::setUp();
		$this->option = rand_str();
	}
	
	function tearDown() {
		parent::tearDown();
		delete_option($this->option);
	}
	
	function test_option_update() {
		
		$expected = rand_str();
		SiteUpgradeOptionActions::option_update($this->option, $expected);
		$this->assertEquals($expected, get_option($this->option));
		
	}
	
}

class SP_OptionCodeGeneratorTest extends WPTestCase {

    function setUp() {
        $errors = new WP_Error();
        $this->upgrade = new SiteUpgrade($errors);

        $callback = $this->upgrade->actions['option_update'];
        $action =& $callback[0];
        update_option('test_string_option', 'Test string');
        update_option('test_array_option', array(1, 2, 3));
        $_POST['options'] = array('test_string_option', 'test_array_option');
        $this->code = $action->generate('');
    }

    function test_generated_code_syntax() {
        $error = SPTestHelper::is_valid_syntax($this->code);
        $this->assertFalse(is_wp_error($error), (is_wp_error($error))?$error->get_error_message():'Option Update syntax is valid');
    }

    function test_generated_yaml() {
        $upgrade = $this->upgrade;
        eval($this->code);
        $this->assertEquals(count($upgrade->tasks), 2);
        $this->assertEquals($this->upgrade->tasks[0][1], array('test_string_option'=>'Test string'));
        $this->assertEquals($this->upgrade->tasks[1][1], array('test_array_option'=>array(1, 2, 3)));
    }

}

?>
