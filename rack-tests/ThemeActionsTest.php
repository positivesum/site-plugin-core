<?php

class ThemeActionsTest extends PhpRack_Test {
    var $theme_actions;
    var $theme_present;
    var $theme_not_present;
    var $original_theme; // to set the current theme back to original theme
	
	function setUp() {
        $this->theme_actions = new SiteUpgradeThemeActions();
        $this->original_theme = get_theme(get_current_theme());
//		$this->theme_available = array('theme_name'=>'D+H', 'template'=>'twentyten', 'stylesheet'=>'dnh');
        $this->theme_available = array('theme_name'=>'Twenty Ten', 'template'=>'twentyten', 'stylesheet'=>'twentyten');
        $this->theme_not_available = array('theme_name'=>'Back2Black', 'template'=>'back2black', 'stylesheet'=>'back2black');
		
	}
	
	function tearDown() {
		parent::tearDown();
        $this->theme_actions->switch_theme($this->original_theme);
	}
	
    public function getLabel() {
        return 'Theme Actions Test'; // Test Label
    }	
	
    /**
     * To pass the test, the specified theme should exist
     * @return void
     */
	function testThemeExists() {
        if ($this->theme_actions->theme_exists($this->theme_available)) {
            $this->_log("Theme Exists!");
        } else {
            $this->assert->fail("Test Theme Exists just failed");		
        }	
	}
	
    /**
     * To pass the test, the specified theme should not exist
     * @return void
     */
    function testThemeNotExists() {
        if ($this->theme_actions->theme_exists($this->theme_not_available)) {
            $this->assert->fail("Test Theme Not Exists just failed");		
        } else {
            $this->_log("Theme Not Exists!");		
        }	
 	}
	
    /**
     * To pass the test, the theme must be successfully switched
     * @return void
     */
    function testSwitchTheme() {
        $this->theme_actions->switch_theme($this->theme_available);
        if (get_current_theme() === 'Twenty Ten') {
            $this->_log("Test Switch Theme is OK!");
        } else {
            $this->assert->fail("Test Switch Theme just failed");		
        }			

    }
}

?>
