<?php

class SPOptionActionsTest extends WPTestCase {
	
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

class SPOptionCodeGeneratorTest extends SPCodeGeneratorTest {

    function setUp() {
		parent::setUp();        
        update_option('test_string_option', 'Test string');
        update_option('test_array_option', array(1, 2, 3));
        $_POST['options'] = array('test_string_option', 'test_array_option');
        $action = &$this->getAction('option_update');
        $this->code = $action->generate('');
    }

    function test_generated_code_syntax() {
        $this->check_syntax($this->code);
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
