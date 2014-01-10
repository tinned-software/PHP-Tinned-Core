<?php
/*******************************************************************************
 * 
 * @author Gerhard STEINBEIS ( gerhard . steinbeis [at] handykick [.] at )
 * @version 0.1
 * 
 * @package framework
 * 
 * General Framework initialisation
 * 
*******************************************************************************/

echo "<b>Test '".basename(__FILE__)."' ... </b><br/>\n";


// if set delete old logging object
$GLOBALS['DBG'] = NULL;
unset($GLOBALS['DBG']);

// include debug_logging class
include_once(dirname(__FILE__).'/../classes/debug_logging.class.php');


// Create object of logging
$logfile = dirname(__FILE__).'/../../log/framework_test';


// delete log files if exists
$logfiles = glob($logfile.'.*');
foreach($logfiles as $file)
{
    echo "Prepare ... Deleting logfile ... $file<br />\n";
    unlink($file);
}



if(isset($_GET['dbgt']) === FALSE || $_GET['dbgt'] == 'tp1')
{
    //
    // Test page 1
    //
    
    
    if(isset($GLOBALS['_COOKIE']['PHPSESSID']))
    {
        $sessid = $GLOBALS['_COOKIE']['PHPSESSID'];
    }
    
    
    // delete log files if exists
    if(file_exists($logfile.'.0000.log')) unlink($logfile.'.0000.log');
    if(file_exists($logfile.'_$sessid.0000.log')) unlink($logfile.'_$sessid.0000.log');
    if(file_exists($logfile.'_perf.0000.log')) unlink($logfile.'_perf.0000.log');
    if(file_exists($logfile.'.0001.log')) unlink($logfile.'.0001.log');
    if(file_exists($logfile.'_$sessid.0001.log')) unlink($logfile.'_$sessid.0001.log');
    if(file_exists($logfile.'_perf.0001.log')) unlink($logfile.'_perf.0001.log');
    
    
    // check if the file exists
    if(file_exists($logfile.'.0000.log') === TRUE || file_exists($logfile.'.0001.log') === TRUE)
    {
        echo "Testing ... Check log file creation ... <b><font color=red>FAILED</font></b><br/>\n";
        echo "Testing ... Please remove all logfile for this test.<br/>\n";
        echo "<b>Test '".basename(__FILE__)."' ... Finished - <font color=red>FAILED</font></b><br/><br/>\n";
        return;
    }
    
    
    
    // create log object
    $GLOBALS['DBG'] = new Debug_Logging(TRUE, $logfile, FALSE);
    $GLOBALS['DBG']->set_field_seperator('|');
    //$GLOBALS['DBG']->dbg_intern = 1;
    
    
    
    //
    // Check log file creation
    //
    // send first log message
    $GLOBALS['DBG']->log_as_type('TEST', 'Test message of the type TEST');
    $logfile_name = $logfile_name = $logfile.'.0000.log';
    
    // check if file was created
    if(file_exists($logfile_name) === TRUE)
    {
        echo "Testing ... Check log file creation (log_as_type) ... <b><font color=green>PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Check log file creation (log_as_type) ... <b><font color=red>FAILED</font></b><br/>\n";
        echo "Testing ... The logfile was not created. logfile='$logfile_name'.<br/>\n";
        echo "<b>Test '".basename(__FILE__)."' ... Finished - <font color=red>FAILED</font></b><br/><br/>\n";
        return;
    }
    //
    // Check log file creation - END
    //
    
    
    
    //
    // Send a log message of all types (enabled / disabled)
    //
    $type_list = array('TEST1', 'TEST2');
    $logfile_name = $logfile.'.0000.log';
    $logfile_name_perf = $logfile.'_perf.0000.log';
    
    // disable the logging
    $GLOBALS['DBG']->set_logging_types('TEST1'   , FALSE);
    $GLOBALS['DBG']->set_logging_types('TEST2'   , FALSE);
    
    
    // send log messages (disabled)
    $GLOBALS['DBG']->log_as_type('TEST1', 'Test message of the type TEST1 disabled');
    $GLOBALS['DBG']->log_as_type('TEST2', 'Test message of the type TEST2 disabled');
    
    
    // enable the logging
    $GLOBALS['DBG']->set_logging_types('TEST1', TRUE);
    $GLOBALS['DBG']->set_logging_types('TEST2', TRUE);
    
    
    // send log messages (enabled)
    $GLOBALS['DBG']->log_as_type('TEST1', 'Test message of the type TEST1 enabled');
    $GLOBALS['DBG']->log_as_type('TEST2', 'Test message of the type TEST2 enabled');
    
    
    // Check for disabled / enabled messages
    $file_content = file_get_contents($logfile_name);
    foreach($type_list as $type)
    {
        // Check log type ...
        if(preg_match( '/Test message of the type '.strtoupper($type).' disabled/', $file_content) == 0 &&
           preg_match( '/Test message of the type '.strtoupper($type).' enabled/', $file_content) >= 0 )
        {
            echo "Testing ... Send ".strtoupper($type)." log message ... <b><font color=green>PASSED</font></b><br/>\n";
        }
        else
        {
            echo "Testing ... Send ".strtoupper($type)." log message ... <b><font color=red>FAILED</font></b><br/>\n";
        }
        
    }
    //
    // Send a log message of all types (enabled / disabled) - END
    //
    
    
    
    //
    // Check filename with sessionid
    //
    $GLOBALS['DBG']->set_sessid_filename(TRUE);
    $GLOBALS['DBG']->log_as_type('TEST1', 'Test message of the type TEST1 enabled');
    $GLOBALS['DBG']->log_as_type('TEST2', 'Test message of the type TEST2 enabled');
    if(file_exists($logfile.'_'.$sessid.'.0000.log') === TRUE)
    {
        echo "Testing ... Log filename with sessionid enabled ... <b><font color=green>PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Log filename with sessionid enabled ... <b><font color=red>FAILED</font></b><br/>\n";
    }
    
    
    
    // test it for the performance log file     
    $GLOBALS['DBG']->set_logfile_suffix('TEST1', '_test1');
    $GLOBALS['DBG']->log_as_type('TEST1', 'Test message of the type TEST1 enabled');
    $GLOBALS['DBG']->log_as_type('TEST2', 'Test message of the type TEST2 enabled');
    if(file_exists($logfile.'_'.$sessid.'_test1.0000.log') === TRUE)
    {
        echo "Testing ... Log filename (suffix='_test1') with sessionid enabled ... <b><font color=green>PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Log filename (suffix='_test1') with sessionid enabled ... <b><font color=red>FAILED</font></b><br/>\n";
    }
    $GLOBALS['DBG']->set_sessid_filename(FALSE);
    
    //
    // Check filename with sessionid - END
    //
    
    
    
    
    // test it for the performance log file     
    $GLOBALS['DBG']->log_as_type('TEST1', 'Test message of the type TEST1 enabled');
    $GLOBALS['DBG']->log_as_type('TEST2', 'Test message of the type TEST2 enabled');
    if(file_exists($logfile.'_test1.0000.log') === TRUE)
    {
        echo "Testing ... Log filename (suffix='_test1') with sessionid enabled ... <b><font color=green>PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Log filename (suffix='_test1') with sessionid enabled ... <b><font color=red>FAILED</font></b><br/>\n";
    }
    
    //
    // Check filename with sessionid - END
    //
    
    
    
    
    echo "Testing ... *** Please check the log file(s) to verify correct logging. ***<br/>\n";
    echo "<b>Test '".basename(__FILE__)."' ... Finished.</b><br/><br/>\n";
    //
    // Test page 1 - END
    //
}



?>