<?php

class CodeGeneratorTest extends PhpRack_Test {

    public static function is_valid_syntax($code) {

        $error_message = '';

        # create temp file
        $tmpfpath = tempnam('/tmp', 'SP');

        #open temp file for writing
        $tmpf = fopen($tmpfpath, 'w');

        # write the code to the temp file
        fwrite($tmpf, "<?php\n$code\n?>");
        fclose($tmpf);

        return CodeGeneratorTest::execute("php -l $tmpfpath");

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

        update_option('test_string_option', 'Test string');
        update_option('test_array_option', array(1, 2, 3));
        $_POST['options'] = array('test_string_option', 'test_array_option');        
        $this->code = $this->upgrade->actions['option_update'][0]->generate('');
    }

    public function getLabel() {
        return 'Code Generator Test'; // Test Label
    }	
	
    function testGeneratedCodeSyntax() {
        $error = CodeGeneratorTest::is_valid_syntax($this->code);
		if (is_wp_error($error)) {
            $this->assert->fail($error->get_error_message());				
		} else {
			$this->_log('Syntax is valid');	
		}
    }	
	
    function testGeneratedYaml() {
        $upgrade = $this->upgrade;
        eval($this->code);
        $this->assertEquals(count($upgrade->tasks), 2);
        $this->assertEquals($this->upgrade->tasks[0][1], array('test_string_option'=>'Test string'));
        $this->assertEquals($this->upgrade->tasks[1][1], array('test_array_option'=>array(1, 2, 3)));
    }

}

?>
