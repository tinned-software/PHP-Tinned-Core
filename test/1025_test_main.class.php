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
include_once(dirname(__FILE__).'/../classes/main.class.php');
include_once(dirname(__FILE__).'/../classes/debug_logging.class.php');
include_once(dirname(__FILE__).'/../classes/debug_profiler.class.php');
include_once(dirname(__FILE__).'/1025_testclass.class.php');




// Define log path and file basename
$logfile = dirname(__FILE__).'/../../log/framework_test';

//
// Step 01
//
$test_description = "Delete old logs";


// delete all log files if exists
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




// prepare Debug_Logging object
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
// Step 01
//
$TO = new TestClass(1, $DBG, $PRF);

$TO->send_debug('Test message of the type DEBUG');
$TO->send_performance('Test message of the type PERFORMANCE');
$TO->send_timer();
$TO->send_timer('TEST', 'Test message of the type TIMER');
$TO->send_memory('TEST', 'Test message of the type MEMORY');
$TO->show_memory('Test message of the type SHOW-MEMORY');
//$TO->hash_test();
$TO = NULL;

declare(ticks = 100);
$PRF->log_memory_info();
$PRF->log_memory_peak();

// the type array
$type_found = array('debug', 'performance', 'timer', 'memory', 'show-memory');
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
if(preg_match('/PEAK START ; \d+\.\d* ; 1025_testclass.class.php ; 7\d/', $file_content) > 0 &&
   preg_match('/PEAK STOP  ; \d+\.\d* ; 1025_testclass.class.php ; 7\d/', $file_content) > 0)
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