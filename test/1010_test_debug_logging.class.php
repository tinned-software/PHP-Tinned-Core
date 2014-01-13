<?php
/**
 * @author Gerhard Steinbeis (info [at] tinned-software [.] net)
 * @copyright Copyright (c) 2008 - 2014, Gerhard Steinbeis
 * @version 0.7.1
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * @package framework
 * @subpackage debug
 * 
 * Debug_Logging class test file
 * 
 * This test script runs tests on the Class.
 * 
**/


echo "<b>Test '".basename(__FILE__)."' ... </b><br/>\n";


// Delete logging object if exists
$GLOBALS['DBG'] = NULL;
$time_difference = array();
unset($GLOBALS['DBG']);


// include debug_logging class (with time measurement)
$timestamp_list['include_start'] = microtime();                              // performance time measurement
include_once(dirname(__FILE__).'/../classes/main.class.php');
include_once(dirname(__FILE__).'/../classes/debug_logging.class.php');
$timestamp_list['include_end'] = microtime();                              // performance time measurement


//
// calculate time difference and average
//
$time_difference['include'] = timestamp_msec($timestamp_list['include_end']) - timestamp_msec($timestamp_list['include_start']);
// save value in session for average calculation
$_SESSION['DBG_'.basename(__FILE__)]['include'][] = $time_difference['include'];
// calculation avrg value
$time_difference['include_avrg'] = array_sum($_SESSION['DBG_'.basename(__FILE__)]['include']) / count($_SESSION['DBG_'.basename(__FILE__)]['include']);
// show loading time
echo "Testing ... Time class file include ... <b><font color=\"blue\">".number_format($time_difference['include'], 5, '.', '')." / ".number_format($time_difference['include_avrg'], 5, '.', '')."</font><font color=\"grey\"> (".count($_SESSION['DBG_'.basename(__FILE__)]['include']).")</font></b><br/>\n";
//
// calculate time difference and average - END
//


// Define log path and file basename
$logfile = dirname(__FILE__).'/../../log/framework_test';



if(isset($_GET['dbgt']) === FALSE || $_GET['dbgt'] == 'tp1')
{
    //
    // Test page 1
    //
    
    
    // get the setssion id for test-checks
    if(isset($GLOBALS['_COOKIE']['PHPSESSID']) === TRUE)
    {
        $sessid = $GLOBALS['_COOKIE']['PHPSESSID'];
    }
    
    
    // delete all log files if exists
    foreach(glob($logfile.'*') as $logfile_name)
    {
        echo "Prepare ... Deleting logfile ... $logfile_name<br />\n";
        unlink($logfile_name);
    }
    
    
    // check if old log files exist
    if(file_exists($logfile.'.0000.log') === TRUE || file_exists($logfile.'.0001.log') === TRUE)
    {
        echo "Testing ... Check log file creation ... <b><font color=\"red\">FAILED</font></b><br/>\n";
        echo "Testing ... Please remove all logfile for this test.<br/>\n";
        echo "<b>Test '".basename(__FILE__)."' ... Finished - <font color=\"red\">FAILED</font></b><br/><br/>\n";
        return;
    }
    
    
    // create log object (with time measurement)
    $timestamp_list['obj_create_start'] = microtime();                              // performance time measurement
    $GLOBALS['DBG'] = new Debug_Logging(TRUE, $logfile, FALSE);
    $timestamp_list['object_create_end'] = microtime();                              // performance time measurement
    
    
    //
    // calculate time difference and average
    //
    $time_difference['object_create'] = timestamp_msec($timestamp_list['object_create_end']) - timestamp_msec($timestamp_list['obj_create_start']);
    // save value in session for avrg calculation
    $_SESSION['DBG_'.basename(__FILE__)]['object_create'][] = $time_difference['object_create'];
    // calculation avrg value
    $time_difference['object_create_avrg'] = array_sum($_SESSION['DBG_'.basename(__FILE__)]['object_create']) / count($_SESSION['DBG_'.basename(__FILE__)]['include']);
    // show loading time
    echo "Testing ... Time Object create ... <b><font color=\"blue\">".number_format($time_difference['object_create'], 5, '.', '')." / ".number_format($time_difference['object_create_avrg'], 5, '.', '')."</font><font color=\"grey\"> (".count($_SESSION['DBG_'.basename(__FILE__)]['object_create']).")</font></b><br/>\n";
    //
    // calculate time difference and average - END
    //
    
    
    // set the field seperator
    $GLOBALS['DBG']->set_field_seperator('|');
    //$GLOBALS['DBG']->dbg_intern = 1;
    
    
    //
    // Check log file creation
    //
    
    // send first log message
    $GLOBALS['DBG']->info('Test message of the type INFO');
    $logfile_name = $logfile.'.0000.log';
    
    // check if file was created
    if(file_exists($logfile_name) === TRUE)
    {
        echo "Testing ... Check log file creation ... <b><font color=\"green\">PASSED</font></b><br/>\n";
        
        // check log file size to be at accptable size
        if(filesize($logfile_name) > 100)
        {
            echo "Testing ... Check log file size ... <b><font color=\"green\">PASSED</font></b><br/>\n";
        }
        else
        {
            echo "Testing ... Check log file size ... <b><font color=\"red\">FAILED</font></b><br/>\n";
            echo "Testing ... The logfile seems to be too small. size='".filesize($logfile_name)."' logfile='$logfile_name'.<br/>\n";
            echo "<b>Test '".basename(__FILE__)."' ... Finished - <font color=\"red\">FAILED</font></b><br/><br/>\n";
            return;
        }
    }
    else
    {
        echo "Testing ... Check log file creation ... <b><font color=\"red\">FAILED</font></b><br/>\n";
        echo "Testing ... The logfile was not created. logfile='$logfile_name'.<br/>\n";
        echo "<b>Test '".basename(__FILE__)."' ... Finished - <font color=\"red\">FAILED</font></b><br/><br/>\n";
        return;
    }
    //
    // Check log file creation - END
    //
    
    
    
    //
    // Check log file creation for performance
    //
    // send first log message for performance log file
    $GLOBALS['DBG']->performance('', 'start');
    $GLOBALS['DBG']->performance('Test message for set_sessid_filename_performance', 'stop');
    $logfile_name_perf = $logfile.'_perf.0000.log';
    
    // check if file was created
    if(file_exists($logfile_name_perf) === TRUE)
    {
        echo "Testing ... Check log file creation ... <b><font color=\"green\">PASSED</font></b><br/>\n";
        
        // check log file size to be at accptable size
        if(filesize($logfile_name_perf) > 100)
        {
            echo "Testing ... Check log file size ... <b><font color=\"green\">PASSED</font></b><br/>\n";
        }
        else
        {
            echo "Testing ... Check log file size ... <b><font color=\"red\">FAILED</font></b><br/>\n";
            echo "Testing ... The logfile seems to be too small. size='".filesize($logfile_name_perf)."' logfile='$logfile_name_perf'.<br/>\n";
        }
    }
    else
    {
        echo "Testing ... Check log file creation ... <b><font color=\"red\">FAILED</font></b><br/>\n";
        echo "Testing ... The logfile was not created. logfile='$logfile_name_perf'.<br/>\n";
    }
    //
    // Check log file creation for performance - END
    //
    
    
    
    
    //
    // Check file size settings
    //
    $GLOBALS['DBG']->set_max_filesize('93KB');
    if($GLOBALS['DBG']->get_max_filesize() == 93000)
    {
        echo "Testing ... Max file size set to '93KB' ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Max file size set to '93KB' ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    
    
    $GLOBALS['DBG']->set_max_filesize('7MB');
    if($GLOBALS['DBG']->get_max_filesize() == 7000000)
    {
        echo "Testing ... Max file size set to '7MB' ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Max file size set to '7MB' ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    
    
    $GLOBALS['DBG']->set_max_filesize('1500');
    if($GLOBALS['DBG']->get_max_filesize() == 1500)
    {
        echo "Testing ... Max file size set to '1500' ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Max file size set to '1500' ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    //
    // Check file size settings - END
    //
    
    
    
    //
    // Check the diff time calculation
    //
    $GLOBALS['DBG']->set_time_diff('script');
    if($GLOBALS['DBG']->get_time_diff() == 'script')
    {
        echo "Testing ... Difference calculation setting 'script' ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Difference calculation setting 'script' ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    
    $GLOBALS['DBG']->set_time_diff('line');
    if($GLOBALS['DBG']->get_time_diff() == 'line')
    {
        echo "Testing ... Difference calculation setting 'line' ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Difference calculation setting 'line' ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    //
    // Check the diff time calculation - END
    //
    
    
    
    //
    // Check filename with sessionid
    //
    $GLOBALS['DBG']->set_sessid_filename(TRUE);
    $GLOBALS['DBG']->info('Test message for set_sessid_filename');
    if(file_exists($logfile.'_'.$sessid.'.0000.log') === TRUE)
    {
        echo "Testing ... Log filename with sessionid enabled ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Log filename with sessionid enabled ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    
    // test it for the performance log file
    $GLOBALS['DBG']->performance('', 'start');
    $GLOBALS['DBG']->performance('Test message for set_sessid_filename_performance', 'stop');
    if(file_exists($logfile.'_'.$sessid.'_perf.0000.log') === TRUE)
    {
        echo "Testing ... Log filename (performance) with sessionid enabled ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Log filename (performance) with sessionid enabled ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    $GLOBALS["DBG"]->set_sessid_filename(FALSE);
    
    //
    // Check filename with sessionid - END
    //
    
    
    
    
    //
    // Send a log message of all types (enabled / disabled)
    //
    $type_list = array('info', 'debug', 'debug_array', 'error', 'debug2', 'debug2_array', 'backtrace');
    $logfile_name = $logfile.'.0000.log';
    $logfile_name_perf = $logfile.'_perf.0000.log';
    
    // disable the logging of all types
    $GLOBALS['DBG']->set_logging_types('info'          , FALSE);
    $GLOBALS['DBG']->set_logging_types('debug'         , FALSE);
    $GLOBALS['DBG']->set_logging_types('debug_array'   , FALSE);
    $GLOBALS['DBG']->set_logging_types('error'         , FALSE);
    $GLOBALS['DBG']->set_logging_types('debug2'        , FALSE);
    $GLOBALS['DBG']->set_logging_types('debug2_array'  , FALSE);
    $GLOBALS['DBG']->set_logging_types('backtrace'     , FALSE);
    $GLOBALS['DBG']->set_logging_types('trace_include' , FALSE);
    $GLOBALS['DBG']->set_logging_types('trace_classes' , FALSE);
    $GLOBALS['DBG']->set_logging_types('trace_function', FALSE);
    // performance type
    $GLOBALS['DBG']->set_logging_types('performance'   , FALSE);
    
    
    // send log messages
    $GLOBALS['DBG']->info(           'Test message of the type INFO disabled');
    $GLOBALS['DBG']->debug(          'Test message of the type DEBUG disabled');
    $GLOBALS['DBG']->debug2(         'Test message of the type DEBUG2 disabled');
    $GLOBALS['DBG']->error(          'Test message of the type ERROR disabled');
    $GLOBALS['DBG']->backtrace(      'Test message of the type BACKTRACE disabled');
    //$GLOBALS['DBG']->trace_include(  'Test message of the type TRACE_INCLUDE disabled');        // Moved to Refrection class
    //$GLOBALS['DBG']->trace_functions('Test message of the type TRACE_FUNCTIONS disabled');      // Moved to Refrection class
    //$GLOBALS['DBG']->trace_classes(  'Test message of the type TRACE_CLASSES disabled');        // Moved to Refrection class
    $GLOBALS['DBG']->debug_array(    'Test message of the type DEBUG_ARRAY disabled', $GLOBALS['_SERVER']);
    $GLOBALS['DBG']->debug2_array(   'Test message of the type DEBUG2_ARRAY disabled', $GLOBALS['_SERVER']);
    // performance log entry
    $GLOBALS['DBG']->performance('', 'start');
    $GLOBALS['DBG']->performance('Test message of the type PERFORMANCE disabled', 'stop');
    
    
    // disable the logging of all types
    $GLOBALS['DBG']->set_logging_types('info'          , TRUE);
    $GLOBALS['DBG']->set_logging_types('debug'         , TRUE);
    $GLOBALS['DBG']->set_logging_types('debug_array'   , TRUE);
    $GLOBALS['DBG']->set_logging_types('error'         , TRUE);
    $GLOBALS['DBG']->set_logging_types('debug2'        , TRUE);
    $GLOBALS['DBG']->set_logging_types('debug2_array'  , TRUE);
    $GLOBALS['DBG']->set_logging_types('backtrace'     , TRUE);
    // performance type
    $GLOBALS['DBG']->set_logging_types('performance'   , TRUE);
    
    
    // send log messages
    $timestamp_list['INFO_start'] = microtime();
    $GLOBALS['DBG']->info(           'Test message of the type INFO enabled');
    $timestamp_list['INFO_end'] = microtime();
    
    $timestamp_list['DEBUG_start'] = microtime();
    $GLOBALS['DBG']->debug(          'Test message of the type DEBUG enabled');
    $timestamp_list['DEBUG_end'] = microtime();
    
    $timestamp_list['DEBUG2_start'] = microtime();
    $GLOBALS['DBG']->debug2(         'Test message of the type DEBUG2 enabled');
    $timestamp_list['DEBUG2_end'] = microtime();
    
    $timestamp_list['ERROR_start'] = microtime();
    $GLOBALS['DBG']->error(          'Test message of the type ERROR enabled');
    $timestamp_list['ERROR_end'] = microtime();
    
    $timestamp_list['BACKTRACE_start'] = microtime();
    $GLOBALS['DBG']->backtrace(      'Test message of the type BACKTRACE enabled');
    $timestamp_list['BACKTRACE_end'] = microtime();
    
    $timestamp_list['DEBUG_ARRAY_start'] = microtime();
    $GLOBALS['DBG']->debug_array(    'Test message of the type DEBUG_ARRAY enabled', $GLOBALS['_SERVER']);
    $timestamp_list['DEBUG_ARRAY_end'] = microtime();
    
    $timestamp_list['DEBUG2_ARRAY_start'] = microtime();
    $GLOBALS['DBG']->debug2_array(   'Test message of the type DEBUG2_ARRAY enabled', $GLOBALS['_SERVER']);
    $timestamp_list['DEBUG2_ARRAY_end'] = microtime();
    
    // performance log entry
    $timestamp_list['PERFORMANCE_start'] = microtime();
    $GLOBALS['DBG']->performance('', 'start');
    $GLOBALS['DBG']->performance('Test message of the type PERFORMANCE enabled', 'stop');
    $timestamp_list['PERFORMANCE_end'] = microtime();
    
    
    
    
    
    // Check for disabled / enabled messages
    $file_content = file_get_contents($logfile_name_perf);
    foreach($type_list as $type)
    {
        // Check log type ...
        if(preg_match('/Test message of the type '.strtoupper($type).' disabled/', $file_content) == 0 &&
           preg_match('/Test message of the type '.strtoupper($type).' enabled/', $file_content) >= 0)
        {
            echo "Testing ... Send ".strtoupper($type)." log message ... <b><font color=\"green\">PASSED</font></b><br/>\n";
        }
        else
        {
            echo "Testing ... Send ".strtoupper($type)." log message ... <b><font color=\"red\">FAILED</font></b><br/>\n";
        }
    }
    
    
    // Check log type performance
    $type = 'performance';
    $file_content = file_get_contents($logfile_name_perf);
    if(preg_match('/Test message of the type '.strtoupper($type).' disabled/', $file_content) == 0 &&
       preg_match('/Test message of the type '.strtoupper($type).' enabled/', $file_content) >= 0)
    {
        echo "Testing ... Send ".strtoupper($type)." log message ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Send ".strtoupper($type)." log message ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    //
    // Send a log message of all types (enabled / disabled) - END
    //
    
    
    
    
    //
    // Calculate time of method calls calls
    //
    // calculate time difference
    foreach($type_list as $type)
    {
        // calculate time difference
        $time_difference[$type] = timestamp_msec($timestamp_list[strtoupper($type).'_end']) - timestamp_msec($timestamp_list[strtoupper($type).'_start']);
        // save value in session for avrg calculation
        $_SESSION['DBG_'.basename(__FILE__)][$type][] = $time_difference[$type];
        // calculation avrg value
        $time_difference[$type.'_avrg'] = array_sum($_SESSION['DBG_'.basename(__FILE__)][$type]) / count($_SESSION['DBG_'.basename(__FILE__)][$type]);
        // show loading time
        echo "Testing ... Time ".strtoupper($type)." log message ... <b><font color=\"blue\">".number_format($time_difference[$type], 5, '.', '')." / ".number_format($time_difference[$type.'_avrg'], 5, '.', '')."</font><font color=\"grey\"> (".count($_SESSION['DBG_'.basename(__FILE__)][$type]).")</font></b><br/>\n";
    }
    
    
    // calculate time difference
    $type = 'performance';
    $time_difference[$type] = timestamp_msec($timestamp_list[strtoupper($type).'_end']) - timestamp_msec($timestamp_list[strtoupper($type).'_start']);
    // save value in session for avrg calculation
    $_SESSION['DBG_'.basename(__FILE__)][$type][] = $time_difference[$type];
    // calculation avrg value
    $time_difference[$type.'_avrg'] = array_sum($_SESSION['DBG_'.basename(__FILE__)][$type]) / count($_SESSION['DBG_'.basename(__FILE__)][$type]);
    // show loading time
    echo "Testing ... Time ".strtoupper($type)." log message ... <b><font color=\"blue\">".number_format($time_difference[$type], 5, '.', '')." / ".number_format($time_difference[$type.'_avrg'], 5, '.', '')."</font><font color=\"grey\"> (".count($_SESSION['DBG_'.basename(__FILE__)][$type]).")</font></b><br/>\n";
    //
    // Calculate time of method calls calls
    //
    
    
    
    
    echo "Testing ... *** Please click this link to check <b><a href=\"?test=".basename(__FILE__)."&amp;dbgt=tp2\">continue the test</a></b>. ***<br/>\n";
    //
    // Test page 1 - END
    //
}
else if(isset($_GET['dbgt']) === TRUE && $_GET['dbgt'] == 'tp2')
{
    unset($_SESSION['DBG_'.basename(__FILE__)]);
    //
    // Test page 2
    //
    $GLOBALS['DBG'] = new Debug_Logging(true, $logfile, false);
    $GLOBALS['DBG']->set_max_filesize('1500');
    $GLOBALS['DBG']->set_field_seperator('|');
    $logfile_name = $logfile.'.0001.log';
    
    
    //
    // Check log file switching if log file size exceeded
    //
    
    // send first log message
    $GLOBALS['DBG']->backtrace('Test message of type BACKTRACE');
    
    // check if file was created
    if(file_exists($logfile_name) === TRUE)
    {
        echo "Testing ... Check log file creation ... <b><font color=\"green\">PASSED</font></b><br/>\n";
        
        // check log file size to be at accptable size
        if(filesize($logfile_name) > 100 )
        {
            echo "Testing ... Check log file size ... <b><font color=\"green\">PASSED</font></b><br/>\n";
        }
        else
        {
            echo "Testing ... Check log file size ... <b><font color=\"red\">FAILED</font></b><br/>\n";
            echo "Testing ... The logfile seems to be too small. size='".filesize($logfile_name)."' logfile='$logfile_name'.<br/>\n";
            echo "<b>Test '".basename(__FILE__)."' ... Finished - <font color=\"red\">FAILED</font></b><br/><br/>\n";
        }
    }
    else
    {
        echo "Testing ... Check log file creation ... <b><font color=\"red\">FAILED</font></b><br/>\n";
        echo "Testing ... The logfile was not created. logfile='$logfile_name'.<br/>\n";
        echo "<b>Test '".basename(__FILE__)."' ... Finished - <font color=\"red\">FAILED</font></b><br/><br/>\n";
    }
    
    $logfile0 = $logfile.'.0000.log';
    $logfile1 = $logfile.'.0001.log';
    if(file_exists($logfile0) === TRUE && filesize($logfile0) > 1500)
    {
        echo "Testing ... Check log file ".basename($logfile0)." size ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Check log file ".basename($logfile0)." size ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    if(file_exists($logfile1) === TRUE && filesize($logfile1) > 100)
    {
        echo "Testing ... Check log file ".basename($logfile1)." size ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Check log file ".basename($logfile1)." size ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    //
    // Check log file switching if log file size exceeded - END
    //
    
    
    
    //
    // Check the different settings for enable / disable log fields
    //
    
    // disable all fields
    $GLOBALS['DBG']->set_logging_fields('datetime' , FALSE);
    $GLOBALS['DBG']->set_logging_fields('timediff' , FALSE);
    $GLOBALS['DBG']->set_logging_fields('sessionid', FALSE);
    $GLOBALS['DBG']->set_logging_fields('ip'       , FALSE);
    $GLOBALS['DBG']->set_logging_fields('type'     , FALSE);
    $GLOBALS['DBG']->set_logging_fields('line'     , FALSE);
    $GLOBALS['DBG']->set_logging_fields('file'     , FALSE);
    $GLOBALS['DBG']->set_logging_fields('function' , FALSE);
    
    // enable field by field
    $GLOBALS['DBG']->debug('Test message of the type DEBUG field 00 Test');
    $GLOBALS['DBG']->set_logging_fields('datetime' , TRUE);
    $GLOBALS['DBG']->debug('Test message of the type DEBUG field 01 Test');
    $GLOBALS['DBG']->set_logging_fields('timediff' , TRUE);
    $GLOBALS['DBG']->debug('Test message of the type DEBUG field 02 Test');
    $GLOBALS['DBG']->set_logging_fields('sessionid', TRUE);
    $GLOBALS['DBG']->debug('Test message of the type DEBUG field 03 Test');
    $GLOBALS['DBG']->set_logging_fields('ip'       , TRUE);
    $GLOBALS['DBG']->debug('Test message of the type DEBUG field 04 Test');
    $GLOBALS['DBG']->set_logging_fields('type'     , TRUE);
    $GLOBALS['DBG']->debug('Test message of the type DEBUG field 05 Test');
    $GLOBALS['DBG']->set_logging_fields('line'     , TRUE);
    $GLOBALS['DBG']->debug('Test message of the type DEBUG field 06 Test');
    $GLOBALS['DBG']->set_logging_fields('file'     , TRUE);
    $GLOBALS['DBG']->debug('Test message of the type DEBUG field 07 Test');
    $GLOBALS['DBG']->set_logging_fields('function' , TRUE);
    $GLOBALS['DBG']->debug('Test message of the type DEBUG field 08 Test');
    
    
    
    // starting the log message cach test
    $GLOBALS['DBG']->set_logcach('*', TRUE, 'browser');
    $GLOBALS['DBG']->set_logcach('CLIENT', TRUE, 'browser');
    
    
    
    // info fields datetime, timediff, sessionid, ip, type, line, file, function
    $logfile_content = file_get_contents($logfile_name);
    $logfile_content = preg_split('/\n/', $logfile_content);
    $GLOBALS['DBG']->debug('Test fields .. Loaded file = '.$logfile_name);
    
    for($fct=0; $fct < 9; $fct++)
    {
        // initialize variables
        $datetime_pattern    = $timediff_pattern    = $sessionid_pattern   = $ip_pattern          = '';
        $type_pattern        = $line_pattern        = $file_pattern        = $function_pattern    = '';
        
        // set variable according to the test.
        if($fct >= 1 ) $datetime_pattern    = '\d\d\d\d\-\d\d\-\d\d\@\d\d\:\d\d\:\d\d\.\d\d\d\d\s+| ';
        if($fct >= 2 ) $timediff_pattern    = '\s+\d+.\d\d\d\d | ';
        if($fct >= 3 ) $sessionid_pattern   = $GLOBALS['_COOKIE']['PHPSESSID'] . '\s+| ';
        if($fct >= 4 ) $ip_pattern          = $GLOBALS['_SERVER']['REMOTE_ADDR'] . '\s+| ';
        if($fct >= 5 ) $type_pattern        = 'DEBUG\s+| ';
        if($fct >= 6 ) $line_pattern        = '\s+\d+ | ';
        if($fct >= 7 ) $file_pattern        = preg_replace('/\./', '\\.', basename(__FILE__)) . '\s+| ';
        if($fct >= 8 ) $function_pattern    = '---\s+| ';
        
        // combine the search pattern for all fields.
        $fields_pattern = $datetime_pattern.$timediff_pattern.$sessionid_pattern.$ip_pattern.$type_pattern.$line_pattern.$file_pattern.$function_pattern;
        $search_pattern = '/^'.$fields_pattern.'Test message of the type DEBUG field 0$fct Test$/';
        
        // replace the regex symbol '|' with '\|'
        $search_pattern = preg_replace('/\|/', '\\|',  $search_pattern);
        
        $GLOBALS['DBG']->debug("Test field 0$fct .. Search pattern = $search_pattern");
        
        // check patern
        $found = 0;
        foreach($logfile_content as $line)
        {
            
            if(preg_match( $search_pattern, $line ) >= 0)
            {
                $found = 1;
                echo "Testing ... Send DEBUG log message with 0$fct info fields ... <b><font color=\"green\">PASSED</font></b><br/>\n";
                break;
            }
        }
        
        if($found == 0)
        {
            echo "Testing ... Send DEBUG log message with 0$fct info fields ... <b><font color=\"red\">FAILED</font></b><br/>\n";
        }
    }
    //
    // Check the different settings for enable / disable log fields - END
    //
    
    
    
    //
    // Check different settings for field seperator
    //
    $GLOBALS['DBG']->set_field_seperator(';');
    $GLOBALS['DBG']->debug('Check for the first field seperator');
    $GLOBALS['DBG']->set_field_seperator('xx');
    $GLOBALS['DBG']->debug('Check for the second field seperator');
    $GLOBALS['DBG']->set_field_seperator('|');
    
    $datetime_pattern    = '\d\d\d\d\-\d\d\-\d\d\@\d\d\:\d\d\:\d\d\.\d\d\d\d\s*';
    $timediff_pattern    = '\s+\d+.\d\d\d\d';
    $sessionid_pattern   = $GLOBALS['_COOKIE']['PHPSESSID'] . '\s*';
    $ip_pattern          = $GLOBALS['_SERVER']['REMOTE_ADDR'] . '\s*';
    $type_pattern        = 'DEBUG\s*';
    $line_pattern        = '\s+\d+';
    $file_pattern        = preg_replace('/\./', '\\.', basename(__FILE__)) . '\s*';
    $function_pattern    = '---\s*';
    
    // create the search pattern for seperator ;
    $fields_pattern = "$datetime_pattern ; $timediff_pattern ; $sessionid_pattern ; $ip_pattern ; $type_pattern ; $line_pattern ; $file_pattern ; $function_pattern ; ";
    $search_pattern1 = '/^'.$fields_pattern.'Check for the first field seperator$/';
    
    // create the search pattern for seperator xx
    $fields_pattern = "$datetime_pattern xx $timediff_pattern xx $sessionid_pattern xx $ip_pattern xx $type_pattern xx $line_pattern xx $file_pattern xx $function_pattern xx ";
    $search_pattern2 = '/^'.$fields_pattern.'Check for the second field seperator$/';
    
    // replace the regex symbol '|' with '\|'
    $search_pattern = preg_replace("/\|/", "\\|",  $search_pattern);
    
    
    $logfile_content = file_get_contents($logfile_name);
    // Test for the 2 field seperator
    if(preg_match( $search_pattern1, $logfile_content ) == TRUE &&
       preg_match( $search_pattern2, $logfile_content ) == TRUE)
    {
        echo "Testing ... Changed field seperator ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Changed field seperator ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    
    
    //
    // Check different settings for field seperator - END
    //
    
    
    
    //
    // Check the file and path filter functionality
    //
    $GLOBALS['DBG']->set_filepath_filter('/framework/', 'black');
    $GLOBALS["DBG"]->debug('Test filename filter - blacklisted directory "framework"');
    
    $GLOBALS["DBG"]->set_filepath_filter('/'.preg_replace('/\./', '\.', basename(__FILE__)).'/', 'white');
    $GLOBALS["DBG"]->debug('Test filename filter - whitelisted filename "'.basename(__FILE__).'"');
    
    // Check log type ...
    if( preg_match( '/Test filename filter - blacklisted directory "framework"/', file_get_contents($logfile_name) ) == 0 && 
        preg_match( '/Test filename filter - whitelisted filename "'.basename(__FILE__).'"/', file_get_contents($logfile_name) ) >= 0 )
    {
        echo "Testing ... Filename based filter ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Filename based filter ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    //
    // Check the file and path filter functionality - END
    //
    
    
    $GLOBALS['DBG']->client_info();
    
    // check the content of the caching log message functionality
    $logcach_content = $GLOBALS['DBG']->get_logcach_content('*');
    $logcach_count = count($logcach_content);
    if(is_array($logcach_content) == TRUE)
    {
        $logcach_content = join("\n", $logcach_content);
    }
    
    if($logcach_count > 5 &&
       preg_match('/Current URL\:      /', $logcach_content) == TRUE
       )
    {
        echo "Testing ... Log message cach content ... <b><font color=\"green\">PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Log message cach content ... <b><font color=\"red\">FAILED</font></b><br/>\n";
    }
    
    //print_r($GLOBALS["DBG"]);
    
    echo "Testing ... *** Please check the log file(s) to verify correct logging. ***<br/>\n";
    echo "Testing ... *** Please click this link to go <b><a href=\"?test=".basename(__FILE__)."\">back to the first test</a></b>. ***<br/>\n";
    echo "<b>Test '".basename(__FILE__)."' ... Finished.</b><br/><br/>\n";
    //
    // Test page 2 - END
    //

}





function test_function_001($variable1, $variable2=null)
{
    $variable2 = $variable1;
}
function test_function_002($variable1, &$variable2)
{
    $variable2 = $variable1;
}



/**
 * Reset micro timerstamp for timediff calculation
 * 
 * This method is used to get the micro timestamp used for the timediff
 * calcultion. The timestamp is returned as float.
 * 
 * @return float Current time including microseconds
**/
function timestamp_msec($micro_timestamp)
{
    // get the microtime in format '0.######## ##########'
    list($usec, $sec) = explode(' ', $micro_timestamp);
    
    // create the micro timestamp
    $micro_ts = ((float)$usec + (float)$sec); 
    
    return $micro_ts;
}


?>