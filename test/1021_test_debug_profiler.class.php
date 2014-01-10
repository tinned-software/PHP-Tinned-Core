<?php
/**
 * Debug_Logging class test file
 * 
 * This test script runns tests on the Class.
 * 
 * @author Gerhard Steinbeis (info [at] tinned-software [.] net)
 * @copyright Copyright (c) 2008 - 2009, Gerhard Steinbeis
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * 
 * @package framework
 * @subpackage debug
 * 
 * @version 0.7
 * 
 * 
**/

framework_test_show("START");

// Delete logging object if exists
$GLOBALS['DBG'] = NULL;
unset($GLOBALS['DBG']);


// include debug_logging class (with time measurement)
include_once(dirname(__FILE__).'/../classes/debug_logging.class.php');
include_once(dirname(__FILE__).'/../classes/debug_profiler.class.php');

// Define test data
$data = "TG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQsIGNvbnNldGV0dXIgc2FkaXBzY2luZyBlbGl0ciwgc2VkIGRpYW0gbm9udW15IGVpcm1vZCB0ZW1wb3IgaW52aWR1bnQgdXQgbGFib3JlIGV0IGRvbG9yZSBtYWduYSBhbGlxdXlhbSBlcmF0LCBzZWQgZGlhbSB2b2x1cHR1YS4gQXQgdmVybyBlb3MgZXQgYWNjdXNhbSBldCBqdXN0byBkdW8gZG9sb3JlcyBldCBlYSByZWJ1bS4gU3RldCBjbGl0YSBrYXNkIGd1YmVyZ3Jlbiwgbm8gc2VhIHRha2ltYXRhIHNhbmN0dXMgZXN0IExvcmVtIGlwc3VtIGRvbG9yIHNpdCBhbWV0LiBMb3JlbSBpcHN1bSBkb2xvciBzaXQgYW1ldCwgY29uc2V0ZXR1ciBzYWRpcHNjaW5nIGVsaXRyLCBzZWQgZGlhbSBub251bXkgZWlybW9kIHRlbXBvciBpbnZpZHVudCB1dCBsYWJvcmUgZXQgZG9sb3JlIG1hZ25hIGFsaXF1eWFtIGVyYXQsIHNlZCBkaWFtIHZvbHVwdHVhLiBBdCB2ZXJvIGVvcyBldCBhY2N1c2FtIGV0IGp1c3RvIGR1byBkb2xvcmVzIGV0IGVhIHJlYnVtLiBTdGV0IGNsaXRhIGthc2QgZ3ViZXJncmVuLCBubyBzZWEgdGFraW1hdGEgc2FuY3R1cyBlc3QgTG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQuIExvcmVtIGlwc3VtIGRvbG9yIHNpdCBhbWV0LCBjb25zZXRldHVyIHNhZGlwc2NpbmcgZWxpdHIsIHNlZCBkaWFtIG5vbnVteSBlaXJtb2QgdGVtcG9yIGludmlkdW50IHV0IGxhYm9yZSBldCBkb2xvcmUgbWFnbmEgYWxpcXV5YW0gZXJhdCwgc2VkIGRpYW0gdm9sdXB0dWEuIEF0IHZlcm8gZW9zrwdcn8w6erg6";


// Define log path and file basename
$logfile = dirname(__FILE__).'/../../log/framework_test';



//
// Step 01 - Delete old logfiles
//
$test_description = "Delete old logs";

// delete all log files if exists
$logfile_list = '';
foreach(glob($logfile.'*') as $logfile_name)
{
    $logfile_list .= $logfile_name."\n";
    unlink($logfile_name);
}
framework_test_show('INFO', $test_description, 'Deleting logfile ', $logfile_list);

// check if old log files exist
if(file_exists($logfile.'.0000.log') === TRUE || file_exists($logfile.'.0001.log') === TRUE)
{
    framework_test_show('FAILED', $test_description);
    framework_test_show('END');
    return;
}
else
{
    framework_test_show('PASSED', $test_description);
}



//
// prepare Debug_Logging object
//
$DBG = new Debug_Logging(TRUE, $logfile, FALSE);
$DBG->set_logging_fields('datetime' , FALSE);
$DBG->set_logging_fields('sessionid' , FALSE);

$DBG->debug("Initial Log Entry! followed by 1 second sleep.");
sleep(1);

// prepare Debug_Profiler object
$PRF = new Debug_Profiler(1, $DBG);

// start tick measurement
register_tick_function(array(&$PRF, 'memory_peak_tick'), TRUE);
declare(ticks = 1);




//
// TEST 02 - test timer measurements
//
$name = 'TIMER';
$text = 'Test message of the type TIMER';
$PRF->timer_start($name, $text);
$array = array($data);
for($i = 0; $i < 50; $i++)
{
    $array[] = hash('sha512', print_r($array, TRUE));
}
$PRF->timer_stop($name, $text);

//
// TEST 03 - test memory measurements without name
//
$name = '*MAIN*';
$text = '-';
$PRF->memory_start($name, $text);
$content_array = array();
for($i = 0; $i < 100; $i++)
{
    // without adding the random content, the 
    // content does not get copied.
    $content_array[] = $data.rand(100, 999);
}
$content_array = NULL;
usleep(150);
$PRF->memory_stop($name, $text);


//
// TEST 04 - test the memory measurements with name
//
$name = 'MEMORY';
$text = 'Test message of the type MEMORY';
$PRF->memory_start($name, $text);
$content_array = array();
for($i = 0; $i < 100; $i++)
{
    // without adding the random content, the 
    // content does not get copied.
    $content_array[] = $data.rand(100, 999);
}
$content_array = NULL;
usleep(150);
$PRF->memory_stop($name, $text);

//
// TEST 05 - test the show memory measurement
//
$PRF->memory_show('Test message of the type SHOW-MEMORY');

declare(ticks = 100);
$PRF->log_memory_info();
$PRF->log_memory_peak();


//
// RESULT processing
//

// the type array
$type_found = array('timer', 'memory', 'show-memory');
$type_not_found = array('md5', 'sha512');
// the logfile names
$logfile_name = $logfile.'.0000.log';
$logfile_name_perf = $logfile.'_perf.0000.log';

// Check for disabled / enabled messages
$file_content = @file_get_contents($logfile_name);
$file_content .= @file_get_contents($logfile_name_perf);
foreach($type_found as $type)
{
    $test_description = "Messages ".strtoupper($type)." found";
    // Check log type ...
    if(preg_match('/Test message of the type '.strtoupper($type).'/', $file_content) > 0)
    {
        framework_test_show('PASSED', $test_description);
    }
    else
    {
        framework_test_show('FAILED', $test_description);
    }
}
foreach($type_not_found as $type)
{
    $test_description = "Message ".strtoupper($type)." not found";
    // Check log type ...
    if(preg_match('/Test message of the type '.strtoupper($type).'/', $file_content) <= 0)
    {
        framework_test_show('PASSED', $test_description);
    }
    else
    {
        framework_test_show('FAILED', $test_description);
    }
}

$test_description = "Correct memory peak detection";
if(preg_match('/PEAK START ; \d+\.\d* ; 1021_test_debug_profiler.class.php ; 1[23]\d/', $file_content) > 0 &&
   preg_match('/PEAK STOP  ; \d+\.\d* ; 1021_test_debug_profiler.class.php ; 1[23]\d/', $file_content) > 0)
{
    framework_test_show('PASSED', $test_description);
}
else
{
    framework_test_show('FAILED', $test_description);
}




framework_test_show('INFO', 'Logfile content', NULL, "\n<pre>".$file_content."</pre>");

framework_test_show('END');





?>