<?php 
/**
 * Function for the framework_test script
 *
 * This function is used to show the test script results to the browser.
 *
 * @author Gerhard Steinbeis (info [at] tinned-software [dot] net)
 * @copyright Copyright (c) 2010
 * @version 0.2
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * @package framework
 * @subpackage test
 *
**/


/**
 * Function to show test script results
 * 
 * This function is used to show the test result to the browser. The type END 
 * will show as well a basic statistic about the tests. (Total, failed, passed)
 * 
 * @param $type The type of the message (PASSED, FAILED, INFO, END)
 * @param $test_description The description text of the text 
 * @param $info1 For type INFO the information to be shown (blue color)
 * @param $info2 For type INFO the information to be shown (grey color)
**/
function test_show($type, $test_description = '[UNDEFINED]', $info1 = NULL, $info2 = NULL)
{
    // define the width of the "Testing ... " lines
    $width  = 70;
    // define the width of the info2 in second line
    $width2 = 11;
    // define seperation character
    $sepchar = '.';
    $fill_chars = '';

    // static counters and timers
    static $test_start;
    static $failed_count;
    static $passed_count;
    static $odd_line;

    // Mark odd lines with different fill characters
    if($odd_line !== FALSE)
    {
        $odd_line = FALSE;
        $line_color = '<font style="background-color: #dddddd;">';
    }
    else
    {
        $odd_line = TRUE;
        $line_color = '<font style="background-color: #ffffff;">';
    }

    //calculate seperation characters
    $text_length = strlen(strip_tags("Testing ... $test_description "));
    $fill_length = ($width - $text_length);
    if($fill_length <= 2)
    {
        $fill_length = 3;
    }
    $fill_chars = str_repeat($sepchar, ($width - $text_length));
    // html content needs one more dot to look equal
    if($text_length !== strlen("Testing ... $test_description "))
    {
        $fill_chars .= $sepchar;  
    }


    // Do the different types
    switch (strtoupper($type))
    {
        case 'PASSED':
            $passed_count++;
            echo $line_color."Testing ... $test_description ".$fill_chars." <b><font color=\"green\">PASSED</font></b></font><br/>\n";
            break;
        
        case 'FAILED':
            $failed_count++;
            echo $line_color."Testing ... $test_description ".$fill_chars." <b><font color=\"red\">FAILED</font></b></font><br/>\n";
            break;
        
        case 'INFO':
            // check if both parameters are provided
            if($info1 !== NULL && $info2 !== NULL)
            {
                echo $line_color."Testing ... $test_description ".$fill_chars." <b><font color=\"blue\">".$info1."</font></b></font><br>\n";

                // check if there are newlines in the content
                if(preg_match("/\n/", $info2) >= 1)
                {
                    // split up into lines and process each line
                    $info_lines = preg_split("/\n/", $info2);
                    foreach($info_lines as $line_text)
                    {
                        // ignore empty lines
                        if(empty($line_text) === FALSE)
                        {
                            echo str_pad("", intval($width2 * 6), '&nbsp;')." <b><font color=\"grey\">".$line_text."</font></b><br/>\n";
                        }
                    }
                }
                else
                {
                    // if on ly info2 is provided
                    echo $line_color.str_pad("", intval($width2 * 6), '&nbsp;')." <b><font color=\"grey\">".$info2."</font></b></font><br/>\n";
                }
            }
            else
            {
                // if only info1 is provided
                echo $line_color."Testing ... $test_description ".$fill_chars." <b><font color=\"blue\">".$info1."</font><font color=\"grey\">".$info2."</font></b></font><br/>\n";
            }
            break;
        
        case 'START':
            // get the filename of the script
            $trace = debug_backtrace();
            $filename = basename($trace[0]['file']);

            // script start timer
            $test_start = microtime(TRUE);
            echo "<b>Test '$filename' </b><br/>\n";
            break;

        case 'END':
            // get the filename of the script
            $trace = debug_backtrace();
            $filename = basename($trace[0]['file']);

            // script end time
            $test_end = microtime(TRUE);
            if($failed_count < 1)
            {
                $failed_count = 0;
            }
            if($passed_count < 1)
            {
                $passed_count = 0;
            }
            $total_count = $failed_count + $passed_count;

            // calculate test duration
            $test_duration = number_format($test_end - $test_start, 4, '.', '');

            echo "Test '$filename' - <b><font color=\"blue\">Statistic</font></b> - <font color=\"grey\"><b>Duration: $test_duration | Total: $total_count | Passed: ".$passed_count." | Failed: ".$failed_count."</b></font><br/>\n";
            echo "<b>Test '$filename' - Finished</b><br/><br/>\n";
            break;

    }

    return;
}

?>