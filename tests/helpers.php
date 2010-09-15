<?php

class SPTestHelper {

    /**
     * Verify the provided string is valid php syntax
     * @param  $code string without <?php ?> braces
     * @return bool|WP_Error
     */
    public static function is_valid_syntax($code) {

        $error_message = '';

        # create temp file
        $tmpfpath = tempnam('/tmp', 'SP');

        #open temp file for writing
        $tmpf = fopen($tmpfpath, 'w');

        # write the code to the temp file
        fwrite($tmpf, "<?php\n$code\n?>");
        fclose($tmpf);

        return SPTestHelper::execute("php -l $tmpfpath");

    }

    /**
     * Executes a specified function. Return true if successful, otherwise returns WP_Error
     * @static
     * @param  $cmd to execute
     * @return bool|WP_Error
     */
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

}

