<?php
/*******************************************************************************
 * 
 * @author Gerhard STEINBEIS ( gerhard . steinbeis [at] handykick [.] at )
 * @version 0.4
 * 
 * @package framework
 * 
 * General Framework initialisation
 * 
*******************************************************************************/

echo "<b>Test '".basename(__FILE__)."' ... </b><br/>\n";

include_once(dirname(__FILE__)."/../classes/debug_logging.class.php");
include_once(dirname(__FILE__)."/../classes/debug_reflection.class.php");

// if set delete old logging object
$GLOBALS["DBG"] = null;
unset($GLOBALS["DBG"]);


// Create object of logging
$logfile = dirname(__FILE__)."/../../log/".basename(__FILE__);


session_start();

//
// Test page
//


// delete log files if exists
$logfiles = glob($logfile.".*");
foreach($logfiles as $file)
{
    unlink($file);
}


// check if the file exists
if(file_exists($logfile.".0000.log"))
{
    echo "Testing ... Removing log files ... <b><font color=red>FAILED</font></b><br/>\n";
    echo "Testing ... Please remove all logfile for this test.<br/>\n";
    echo "<b>Test '".basename(__FILE__)."' ... Finished - <font color=red>FAILED</font></b><br/><br/>\n";
    return;
}



// create log object
$GLOBALS["DBG"] = new Debug_Logging(true, $logfile, false);
$GLOBALS["DBG"]->set_field_seperator("|");
$GLOBALS["DBG"]->set_catch_php_errors(true);
$GLOBALS["DBG"]->set_catch_unhandled_exceptions(true);
//$GLOBALS["DBG"]->dbg_intern = 1;

$GLOBALS["DBG"]->log_as_type("*****","Object for logging in Profiler ... initiated and working.");

// create log object
//$GLOBALS["RFL"] = new Debug_Reflection(1, $logfile.".log");
$GLOBALS["RFL"] = new Debug_Reflection(1, $GLOBALS["DBG"]);
//$GLOBALS["RFL"]->dbg_intern = 1;



// log php versions
$GLOBALS["RFL"]->php_version();

// log available and loaded extensions
$GLOBALS["RFL"]->php_extensions();

// log included files
$GLOBALS["RFL"]->php_includes();

// log user functions
$GLOBALS["RFL"]->user_functions();

// log user classes
$GLOBALS["RFL"]->user_classes();

// log php functions
$GLOBALS["RFL"]->php_functions();

// log php classes
$GLOBALS["RFL"]->php_classes();

// log process and os infos
$GLOBALS["RFL"]->process_os();




/*
// Check for disabled / enabled messages
foreach($type_list as $type)
{
    // Check log type ...
    if( preg_match( "/Test message of the type ".strtoupper($type)."/", file_get_contents($logfile_name) ) == false &&
        preg_match( "/Test message of the type ".strtoupper($type)."/", file_get_contents($logfile_name) ) == true )
    {
        echo "Testing ... Send ".strtoupper($type)." log message ... <b><font color=green>PASSED</font></b><br/>\n";
    }
    else
    {
        echo "Testing ... Send ".strtoupper($type)." log message ... <b><font color=red>FAILED</font></b><br/>\n";
    }
    
}

*/

echo "<b>Test '".basename(__FILE__)."' ... Finished.</b><br/><br/>\n";



?>