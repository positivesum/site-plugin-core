<?php
function rand_str($len=32) {
	return substr(md5(uniqid(rand())), 0, $len);
}

class OptionActionsTest extends PhpRack_Test {
	
	function setUp() {
		parent::setUp();
		$this->option = rand_str();
	}
	
	function tearDown() {
		parent::tearDown();
		delete_option($this->option);
	}

   public function getLabel() {
        return 'Option Actions Test'; // Test Label
    }
	
	function testOptionUpdate() {
		$expected = rand_str();
		SiteUpgradeOptionActions::option_update(array($this->option => $expected));
		$this->assertEquals($expected, get_option($this->option));
		
	}
}

?>
