<?php

class SP_ThemeActionsTest extends WPTestCase {
    var $theme_actions;
    var $theme_present;
    var $theme_not_present;
    var $original_theme; // to set the current theme back to original theme
	function setUp() {
		parent::setUp();
        $this->theme_actions = new SiteUpgradeThemeActions();
        $this->original_theme = get_theme(get_current_theme());
		$this->theme_available = array('Name'=>'ACMS', 'Template'=>'acms', 'Stylesheet'=>'acms');
        $this->theme_not_available = array('Name'=>'Back2Black', 'Template'=>'back2black', 'Stylesheet'=>'back2black');
	}
	function tearDown() {
		parent::tearDown();
        $this->theme_actions->switch_theme($this->original_theme);
	}
    /**
     * To pass the test, the specified theme should exist
     * @return void
     */
	function test_theme_exists() {
		$this->assertTrue($this->theme_actions->theme_exists($this->theme_available));
	}
    /**
     * To pass the test, the specified theme should not exist
     * @return void
     */
    function test_theme_not_exists() {
        $this->assertFalse($this->theme_actions->theme_exists($this->theme_not_available));
	}
    /**
     * To pass the test, the theme must be successfully switched
     * @return void
     */
    function test_switch_theme() {
        $this->theme_actions->switch_theme($this->theme_available);
        $this->assertTrue(get_current_theme() === 'ACMS');
    }
}

?>
