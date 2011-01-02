<?php

class ThemeCodeGeneratorTest extends PhpRack_Test {

    public static function is_valid_syntax($code) {

        $error_message = '';

        # create temp file
        $tmpfpath = tempnam('/tmp', 'SP');

        #open temp file for writing
        $tmpf = fopen($tmpfpath, 'w');

        # write the code to the temp file
        fwrite($tmpf, "<?php\n$code\n?>");
        fclose($tmpf);

        return ThemeCodeGeneratorTest::execute("php -l $tmpfpath");

    }
	
    public static function execute($cmd){

        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w") // stderr is a file to write to
        );

        $process = proc_open($cmd, $descriptorspec, $pipes, $cwd = NULL, $env = NULL);
        $error = stream_get_contents($pipes[1]);
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $status = proc_close($process);

        return ( $status == 0 ) ? true : new WP_Error('error', $error);

    }
	
    function setUp() {
		$errors = new WP_Error(); 
        $this->upgrade = new SiteUpgrade($errors);
    }

    public function getLabel() {
        return 'Theme Code Generator Test'; // Test Label
    }	

    /**
     * verify that code is not generated when no theme switch is selected
     * @return void
     */
	
    function testNothemeSwitch(){
        // unset($_POST['theme']);
		$_POST['theme'] = ' ';		
        $code = $this->upgrade->actions['switch_theme'][0]->generate('');
        $this->assertEquals($code, '');
    }
	

    function testThemeSwitchCode(){
        $_POST['theme'] = 'twentyten';
        $code = $this->upgrade->actions['switch_theme'][0]->generate('');
        $error = ThemeCodeGeneratorTest::is_valid_syntax($code);
		if (is_wp_error($error)) {
            $this->assert->fail($error->get_error_message());				
		} else {
			$this->_log('Theme Switch Code is valid');	
		}		
     }

}

?>
