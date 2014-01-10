<?php
/**
 * Debug_Reflection file
 * 
 * Class to provide reflection functionality.
 * 
 * @author Gerhard Steinbeis (info [at] tinned-software [dot] net)
 * @copyright Copyright (c) 2008 - 2009, Gerhard Steinbeis
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * 
 * @package framework
 * @subpackage debug
 * 
 * @version 0.5
 * 
 * @todo Add method to log the phpinfo informations to a (seperate?) logfile.
 * @todo Add function to list the file owner and group of a complete directory 
 * and subdirectories including all php files.
 *      fileowner() - http://us.php.net/manual/en/function.fileowner.php
 *      filegroup() - http://us.php.net/manual/en/function.filegroup.php
**/


date_default_timezone_set("UTC");


/**
 *                                   
 * Debug_Reflection class to provide logging for reflection infirmation.
 * 
 * The Debug_Reflection class provides an easy way to log reflection 
 * information. With This class the informations are stored into a logfile or 
 * into a logging object of the Debug_Logging type.
 * 
 * It is also possible to log a list of classes as well as a list of functions 
 * defined by php code. It is also possible to log the php version and the 
 * loaded php extensions with there functions.
 * 
 * @see Debug_Logging
 * @package framework
 * @subpackage debug
 * 
**/
class Debug_Reflection
{
    ////////////////////////////////////////////////////////////////////////////
    // PROPERTIES of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * @ignore
     * To enable internal logging. This will send log messages of the class to 
     * the browser. Used to debug the class.
     * @access public
     * @var integer
    **/
    public $dbg_intern                 = 0;
    
    
    /**
     * Set the column length for the time-diff field to a minimum length
     * @access public
     * @var integer
    **/
    public $strlen_diff                 = 7;
    /**
     * Set the column length for the session-id field to a minimum length
     * @access public
     * @var integer
    **/
    public $strlen_sess                 = 27;
    /**
     * Set the column length for the ip-address field to a minimum length
     * @access public
     * @var integer
    **/
    public $strlen_ip                   = 15;
    /**
     * Set the column length for the type field to a minimum length
     * @access public
     * @var integer
    **/
    public $strlen_type                 = 8;
    /**
     * Set the column length for the line-number field to a minimum length
     * @access public
     * @var integer
    **/
    public $strlen_line                 = 5;
    /**
     * Set the column length for the file-name field to a minimum length
     * @access public
     * @var integer
    **/
    public $strlen_file                 = 30;
    /**
     * Set the column length for the function-name field to a minimum length
     * @access public
     * @var integer
    **/
    public $strlen_func                 = 27;
    
    
    
    // Set the debug level for the class
    private $class_debug                = -1;           // set to 1 to enable debug output function calls
    
    // used php session id and remote ip address
    private $sessid                     = "";
    private $remote_ip                  = "";
    
    // field seperator
    private $field_seperator            = "|";
    
    // to write empty lines before first log entry
    private $first_log                  = true;
    
    // log filename variables
    private $filename                   = null;
    
    // To store the logging object
    private $logging_object             = null;
    
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // CONSTRUCTOR & DESCTRUCTOR methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Costructor for the class
     * 
     * The constructor accepts the basic configuration for the log filename 
     * or the logging object. The log_object parameter can be the logfile name 
     * or the logging object. 
     * 
     * @see Debug_Logging
     * 
     * @param int $dbg_level Enables additional log messages
     * @param mixed $log_object Debug object to send log messages to or logfile name
    **/
    function __construct ($dbg_level=-1, $log_object=null)
    {
        // print internal debug messages
        if($this->dbg_intern > 0)
        {
            // check if it is a object
            if(is_object($log_object) == true) 
            {
                echo "<pre>".get_class($this)."->".__FUNCTION__." - \$logging_object => is object.</pre>\n";
            }
            else 
            {
                echo "<pre>".get_class($this)."->".__FUNCTION__." - \$logging_object => is NOT object.</pre>\n";
            }
            
            // check if the method exists
            if(method_exists(get_class($log_object), "log_as_type") == true) 
            {
                echo "<pre>".get_class($this)."->".__FUNCTION__." - \$logging_object->log_as_type => method exists.</pre>\n";
            }
            else 
            {
                echo "<pre>".get_class($this)."->".__FUNCTION__." - \$logging_object->log_as_type => method NOT exists.</pre>\n";
            }
        }
        
        
        // Check if logging object is provided
        if( is_object($log_object) && method_exists(get_class($log_object), "log_as_type") )
        {
            if($this->dbg_intern > 0) echo "<pre>".get_class($this)."->".__FUNCTION__." - log_object = ".(int)is_object($this->logging_object)."</pre>\n";
            $this->logging_object =& $log_object;
            
            if($this->dbg_intern > 0) echo "<pre>".get_class($this)."->".__FUNCTION__." - class_debug = 1</pre>\n";
            $this->class_debug = 1;
            
        }
        // Check if logging filename is provided
        else if(is_string($log_object))
        {
            $this->filename = $log_object;
            $this->class_debug = 1;
        }
        
        
        // set the debug level setting
        if($this->dbg_intern > 0) echo "<pre>".get_class($this)."->".__FUNCTION__." - check debug level setting.</pre>\n";
        if($dbg_level > -1)
        {
            if($this->dbg_intern > 0) echo "<pre>".get_class($this)."->".__FUNCTION__." - Set debug level to $dbg_level.</pre>\n";
            $this->class_debug = $dbg_level;
        }
        
        // FIX for PHP issue
        // without this line, The if statement below will always behave as 
        // there is no $GLOBALS["_SERVER"] variable. After the count function, 
        // the if statement works as expected
        count($_SERVER);        // Fix for strange PHP behaviour.
        
        
        // set the current session id to the class if available
        $this->sessid = session_id();
        if($this->dbg_intern >= 1) echo "<pre>".get_class($this)."->".__FUNCTION__." - Set session-id (".$this->sessid.").<br/></pre>\n";
        
        
        // Set the remote IP address
        if(isset($GLOBALS["_SERVER"]["REMOTE_ADDR"]))
        {
            $this->remote_ip = $GLOBALS["_SERVER"]["REMOTE_ADDR"];
            if($this->dbg_intern >= 1) echo "<pre>".get_class($this)."->".__FUNCTION__." - Set remote ip-address (".$this->remote_ip.").<br/></pre>\n";
        }
        
        
    }
    
    
    
    /**
     * Destructor for the class
     * 
     * The descructor will send a last log message to the configured log 
     * targets when the object is destroid. The old error and exception handler will also be restored
    **/
    public function __destruct()
    {
        // no actions
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // SET methods to set class Options
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Set the seperating characters for the log fields
     * 
     * This method sets the seperating character for the log message fields. By 
     * default this character is set to '|'. This character can be changed to 
     * ';' (to be able to handle the logfiles like csv files) or to any other 
     * character.
     * 
     * @see get_field_seperator()
     * 
     * @param string $seperator The seperating character(s) for the log fields
    **/
    public function set_field_seperator($seperator)
    {
        // set the field seperator
        $this->field_seperator = $seperator;
        
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // GET methods to get class Options
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Get the seperating characters for the log fields
     * 
     * This method returns the configured field seperator used to seperate the 
     * log message fields.
     * 
     * @see set_field_seperator()
     * 
     * @return bool The seperating character(s) for the log fields
    **/
    public function get_field_seperator()
    {
        // get the field seperator
        return $this->field_seperator;
        
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // PRIVATE methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Reset micro timerstamp for timediff calculation
     * 
     * This method is used to get the micro timestamp used for the timediff
     * calcultion. The timestamp is returned as float.
     * 
     * @return float Current time including microseconds
    **/
    private function timestamp_msec()
    {
        // get the microtime in format "0.######## ##########"
        list($usec, $sec) = explode(" ",microtime());
        // create the micro timestamp
        $micro_ts = ((float)$usec + (float)$sec); 
        
        return $micro_ts;
    } 
    
    
    
    /**
     * get the current micro time (milli seconds)
     * 
     * This method returns the current time as float including the microtime.
     * 
     * @return int The miliseconds within the current second
    **/
    private function msec()
    {
        // get the microtime in format "0.######## ##########"
        $m = explode(' ',microtime());
        // remove the "0." from the microseconds
        $msec = explode('.', $m[0]);
        // truncate to 4 digits
        $msec_str = substr($msec[1], 0, 4);
        return $msec_str;
    } 
    
    
    
    /**
     * Return human readable sizes
     * 
     * This method formats the given byte size into a human readable format 
     * with the corresbonding prefix.
     * 
     * @param $size The size in bytes to be shown
     * @param $max The maximum unit to show the size
     * @param $system 'si' for SI, 'bi' for binary prefixes
     * @param $retstring return string format formated for sprintf function
     * @return string formated size in human readable format
    **/
    private function size_readable($size, $max = null, $system = 'bi', $retstring = '%01.3f %s')
    {
        // Pick units
        $systems['si']['prefix'] = array('B', 'K', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $systems['si']['size']   = 1000;
        $systems['bi']['prefix'] = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $systems['bi']['size']   = 1024;
        
        // get the requested calculation system
        if(isset($systems[$system]))
        {
            $sys = $systems[$system];
        }
        else
        {
            $sys = $systems['si'];
        }
        
        
        // Max unit to display
        $depth = count($sys['prefix']) - 1;
        $d = array_search($max, $sys['prefix']);
        if ($max != null && $d != false) {
            $depth = $d;
        }
        
        
        // Loop to calculate the size
        $i = 0;
        while ($size >= $sys['size'] && $i < $depth) {
            $size = $size / $sys['size'];
            $i++;
        }
        
        
        // return frmated string
        return sprintf($retstring, $size, $sys['prefix'][$i]);
    }
    
    
    
    /**
     * The main logging method (autonomous logging)
     * 
     * This method is the main logging method. In this method all the log 
     * messages are stored to the logfile. The method takes the message text 
     * and the type. The rest of the information fields are generated with 
     * help of the debug_backtrace() functionality. The collected information 
     * fields will be formated and then sent to the configured log targets.
     * 
     * @link http://www.php.net/manual/en/function.debug-backtrace.php debug_backtrace()
     * 
     * @param string $msg_text Message text.
     * @param string $type Type of log message
     * @param int $add_traceback_index Additional index to trace back
    **/
    private function logging($msg_text, $type, $add_traceback_index=0)
    {
        if($this->dbg_intern > 0) echo "<pre>".get_class($this)."->".__FUNCTION__." - with type=$type; add_traceback_index=$add_traceback_index; msg_text=$msg_text</pre>\n";
        //
        // Object Logging
        //
        
        // if method debug in class exists, call it for logging
        if(is_object($this->logging_object) == true && method_exists(get_class($this->logging_object), "log_as_type") == true)
        {
            if($this->dbg_intern > 0) echo "<pre>".get_class($this)."->".__FUNCTION__." - Call \$this->logging_object->log_as_type()</pre>\n";
            $this->logging_object->log_as_type(strtoupper($type), $msg_text, $add_traceback_index + 1);
            $this->first_log = false;
            return;
        }
        
        //
        // Object Logging - END
        //
        
        
        //
        // Autonomous Logging
        //
        $type = strtoupper($type);
        if($this->dbg_intern > 0) echo "<pre>".get_class($this)."->".__FUNCTION__." - Log autonomously</pre>\n";
        
        //
        // get traceback informations
        //
        if(is_int($add_traceback_index) == false) $add_traceback_index = 0;
        $infoarray  = debug_backtrace();
        $tbi        = 0 + $add_traceback_index;
        
        // check if the requested backtrace object exists. 
        // If not count down till the next existsing is found.
        while(isset($infoarray[$tbi]) === false)
        {
            if(!isset($infoarray[$tbi])) $tbi--;
            if($tbi < 0) $tbi = 0;
        }
        //
        // get traceback informations - END
        //
        
        
        //
        // Prepare backtrace informations
        //
        
        // get file name of the logging source if available
        $file_complete  = "";
        if(isset($infoarray[$tbi]["file"])) $file_complete  = $infoarray[$tbi]["file"];
        $file = basename($file_complete);
        
        // get line number of the logging source if available
        $line  = "";
        if(isset($infoarray[$tbi]["file"])) $line  = $infoarray[$tbi]["line"];
        
        // get function name if available
        $function       = "---";
        if( count($infoarray) > ($tbi + 1) )
        {
            $function   = $infoarray[$tbi + 1]["function"];
        }
        if($function == "include" || $function == "include_once")
        {
            $function   = "---";
        }
        //
        // Prepare backtrace informations - END
        //
        
        // get class variables for field values
        $ip = $this->remote_ip;
        $session = $this->sessid;
        
        
        //
        // Format the content of the logging fields
        //
        $type       = substr($type.         "                                           ", 0, $this->strlen_type);
        $ip         = substr($ip.           "                                           ", 0, $this->strlen_ip);
        $session    = substr($session.      "                                           ", 0, $this->strlen_sess);
        $line       = substr("                                             ".$line       , 0 - $this->strlen_line);
        $time_diff  = substr("                                             "."--.----"  , 0 - $this->strlen_diff);
        if(strlen($function) < $this->strlen_func)
        {
            $function = substr($function."                                                           ", 0, $this->strlen_func);
        }
        if(strlen($file) < $this->strlen_file)
        {
            $file = substr($file.       "                                                            ", 0, $this->strlen_file);
        }
        //
        // Format the content of the logging fields
        //
        
        $date_time = date("Y-m-d@H:i:s");
        
        $fs = $this->field_seperator;
        $Log_message  = "$date_time $fs ";
        $Log_message .= "$time_diff $fs ";
        $Log_message .= "$session $fs ";
        $Log_message .= "$ip $fs ";
        $Log_message .= "$type $fs ";
        $Log_message .= "$line $fs ";
        $Log_message .= "$file $fs ";
        $Log_message .= "$function $fs ";
        
        // write to the logfile
        error_log($Log_message.$msg_text."\n", 3, $this->filename);
        
        //
        // Autonomous Logging - END
        //
        
        
        $this->first_log = false;
        return;
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // PUBLIC methods of the class - PHP-INFO
    // - PHP Information methods
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Log message from type PHP_VERS (php version)
     * 
     * This method gathers available php informations. The php version and the 
     * sapi type. This informations are sent to the configured log target.
    **/
    public function php_version()
    {
        //
        // get the php version
        //
        $php_version = phpversion();
        
        // log the php version
        $this->logging("Running PHP version: $php_version", "PHP_VERS", 1);
        //
        // get the php version - END
        //
        
        
        
        //
        // get the php sapi
        //
        $php_sapi = php_sapi_name();
        
        // log the php version
        $this->logging("Running PHP sapi: $php_sapi", "PHP_VERS", 1);
        //
        // get the php sapi - END
        //
        
        return;
    }
    
    
    
    /**
     * Log message from type PHP_EXT (php extensions)
     * 
     * This method gathers informations of loaded extensions. If the 
     * list_functions parameter is set to true, the functions of each 
     * extension are listed. This informations are sent to the configured log 
     * target.
     * 
     * @param bool $list_functions To log the defined functions per extension
    **/
    public function php_extensions($list_functions=false)
    {
        $this->logging("List of php extensions.", "PHP_EXT", 1);
        
        //
        // get loaded extensions
        //
        $php_extensions = get_loaded_extensions();
        $idxe = 0;
        foreach($php_extensions as $ext_name)
        {
            $idxe++;
            // exclude the 'standard' module as it is contains 500+ basic functions
            if($ext_name == 'standard') continue;
            
            // format the index number
            $idxe = substr("    ".$idxe,-3);
            
            // log the php version
            $this->logging("*** $idxe: Extension '$ext_name' loaded", "PHP_EXT", 1);
            
            // get the extension functions ... if enabled
            if($list_functions == true)
            {
                //
                // get the extension functions
                //
                $ext_functions = get_extension_funcs($ext_name);
                foreach($ext_functions as $idxf => $function)
                {
                    // get the function parameters
                    $param_list = "";
                    $idxp = 1;
                    $rfunction = new ReflectionFunction($function);
                    foreach($rfunction->getParameters() as $param)
                    {
                        if($param->isOptional()) $param_list .= "[";
                        if($param->isPassedByReference()) $param_list .= "&";
                        if($param->getName() == "")
                        {
                            $param_list .= "\$param$idxp";
                            $idxp++;
                        }
                        else
                        {
                            $param_list .= "$".$param->getName();
                        }
                        if($param->isOptional()) $param_list .= "]";
                        $param_list .= ", ";
                    }
                    $param_list = preg_replace("/, $/", "", $param_list);
                    
                    
                    // format the index number
                    $idxf = substr("    ".($idxf + 1),-3);
                    
                    // log the php version
                    $this->logging("***    $idxf: Function $function($param_list)", "PHP_EXT", 1);
                }
                //
                // get the extension functions - END
                //
            }
        }
        //
        // get loaded extensions - END
        //
        
    }
    
    
    
    /**
     * Log message from type INCLUDE (list includes)
     * 
     * This method initiates a complete list of included files to be logged to 
     * the logfile. This log masseges are from the type INCLUDE to indicate 
     * 'list includes'. 
    **/
    public function php_includes()
    {
        $this->logging("List of included files.", "INCLUDE", 1);
        
        $bpath = dirname($GLOBALS["_SERVER"]["SCRIPT_FILENAME"])."/";
        
        // get list of included files
        $list = get_included_files();
        
        $j = 1;
        foreach ($list as $element)
        {
            $element = str_replace("$bpath", "", $element);
            $this->logging("*** $j: File $element included", "INCLUDE", 1);
            $j++;
        }
        
    }
    
    
    
    /**
     * Log message from type U_FUNC (user functions)
     * 
     * This method initiates a complete list of user defined functions to be 
     * logged to the logfile. This log masseges are from the type U_FUNC to 
     * indicate 'user functions'. The functions are listed with there defined 
     * parameters and if thea are optional. 
    **/
    public function user_functions()
    {
        $this->logging("List of user defined functions.", "U_FUNC", 1);
        
        $bpath = dirname($GLOBALS["_SERVER"]["SCRIPT_FILENAME"])."/";
        
        // get list of included files
        $list = get_defined_functions();
        
        $j = 1;
        foreach ($list["user"] as $element)
        {
            $element = str_replace("$bpath", "", $element);
            
            $jstr = substr("    ".$j,-3);
            
            // get the type of function (protected, public, private)
            $element_function = new ReflectionFunction($element);
            
            // get all parameters of the method
            $param_list = "";
            $idxp = 1;
            foreach($element_function->getParameters() as $param)
            {
                if($param->isOptional()) $param_list .= "[";
                if($param->isPassedByReference()) $param_list .= "&";
                if($param->getName() == "")
                {
                    $param_list .= "\$param$idxp";
                    $idxp++;
                }
                else
                {
                    $param_list .= "$".$param->getName();
                }
                if($param->isOptional()) $param_list .= "]";
                $param_list .= ", ";
            }
            $param_list = preg_replace("/, $/", "", $param_list);            
            
            $this->logging("*** $jstr: Function $element($param_list)", "U_FUNC", 1);
            $j++;
        }
        
    }
    
    
    
    /**
     * Log message from type U_CLASS (user classes)
     * 
     * This method initiates a complete list of user defined classes to be 
     * logged to the logfile. This log masseges are from the type U_CLASS to 
     * indicate 'user classes'. The class-methods are listed also with there 
     * defined parameters and if thea are optional. 
    **/
    public function user_classes()
    {
        $this->logging("List of user defined classes.", "U_CLASS", 1);
        
        $bpath = dirname($GLOBALS["_SERVER"]["SCRIPT_FILENAME"])."/";
        
        // get list of included files
        $list = get_declared_classes();
        
        $j = 1;
        foreach ($list as $element)
        {
            $element = str_replace("$bpath", "", $element);
            
            // check if user defined and skip if not.
            $rclass = new ReflectionClass($element);
            if($rclass->isUserDefined() == false) continue;
            
            $jstr = substr("    ".$j,-3);
            $this->logging("*** $jstr: Class $element defined.", "U_CLASS", 1);
            
            $methods = get_class_methods($element);
            $k = 1;
            foreach($methods as $method) 
            {
                // get the type of method (protected, public, private)
                $rclass_method = new ReflectionMethod($element, $method);
                $type = " ";
                if($rclass_method->isPublic()    == true) $type = '*';
                if($rclass_method->isProtected() == true) $type = '+';
                if($rclass_method->isPrivate()   == true) continue;
                
                // get all parameters of the method
                $param_list = "";
                $idxp = 1;
                foreach($rclass_method->getParameters() as $param)
                {
                    if($param->isOptional()) $param_list .= "[";
                    if($param->isPassedByReference()) $param_list .= "&";
                    if($param->getName() == "")
                    {
                        $param_list .= "\$param$idxp";
                        $idxp++;
                    }
                    else
                    {
                        $param_list .= "$".$param->getName();
                    }
                    if($param->isOptional()) $param_list .= "]";
                    $param_list .= ", ";
                }
                $param_list = preg_replace("/, $/", "", $param_list);
                
                $kstr = substr("     ".$k,-4);
                
                $this->logging("***    $kstr: Method $type $method($param_list)", "U_CLASS", 1);
                $k++;
            }
            
            $j++;
        }
        
    }
    
    
    
    /**
     * Log message from type I_FUNC (internal functions)
     * 
     * This method initiates a complete list of php internal functions to be 
     * logged to the logfile. This log masseges are from the type I_FUNC to 
     * indicate 'internal functions'. The functions are listed with there 
     * defined parameters and if thea are optional. 
    **/
    public function php_functions()
    {
        $this->logging("List of php internal functions.", "I_FUNC", 1);
        
        $bpath = dirname($GLOBALS["_SERVER"]["SCRIPT_FILENAME"])."/";
        
        // get list of included files
        $list = get_defined_functions();
        
        $j = 1;
        foreach ($list["internal"] as $element)
        {
            $element = str_replace("$bpath", "", $element);
            
            $jstr = substr("    ".$j,-3);
            
            // get the type of function (protected, public, private)
            $element_function = new ReflectionFunction($element);
            
            // get all parameters of the method
            $param_list = "";
            $idxp = 1;
            foreach($element_function->getParameters() as $param)
            {
                if($param->isOptional()) $param_list .= "[";
                if($param->isPassedByReference()) $param_list .= "&";
                if($param->getName() == "")
                {
                    $param_list .= "\$param$idxp";
                    $idxp++;
                }
                else
                {
                    $param_list .= "$".$param->getName();
                }
                if($param->isOptional()) $param_list .= "]";
                $param_list .= ", ";
            }
            $param_list = preg_replace("/, $/", "", $param_list);            
            
            $this->logging("*** $jstr: Function $element($param_list)", "I_FUNC", 1);
            $j++;
        }
        
    }
    
    
    
    /**
     * Log message from type I_CLASS (internal classes)
     * 
     * This method initiates a complete list of php internal classes to be 
     * logged to the logfile. This log masseges are from the type I_CLASS to 
     * indicate 'internal classes'. The class-methods are listed also with 
     * there defined parameters and if thea are optional. 
    **/
    public function php_classes()
    {
        $this->logging("List of php internal classes.", "I_CLASS", 1);
        
        $bpath = dirname($GLOBALS["_SERVER"]["SCRIPT_FILENAME"])."/";
        
        // get list of included files
        $list = get_declared_classes();
        
        $j = 1;
        foreach ($list as $element)
        {
            $element = str_replace("$bpath", "", $element);
            
            // check if user defined and skip if not.
            $rclass = new ReflectionClass($element);
            if($rclass->isUserDefined() == true) continue;
            
            $jstr = substr("    ".$j,-3);
            $this->logging("*** $jstr: Class $element defined.", "I_CLASS", 1);
            $methods = get_class_methods($element);
            $k = 1;
            foreach($methods as $method) 
            {
                // get the type of method (protected, public, private)
                $rclass_method = new ReflectionMethod($element, $method);
                $type = " ";
                if($rclass_method->isPublic()    == true) $type = '*';
                if($rclass_method->isProtected() == true) $type = '+';
                if($rclass_method->isPrivate()   == true) continue;
                
                // get all parameters of the method
                $param_list = "";
                $idxp = 1;
                foreach($rclass_method->getParameters() as $param)
                {
                    if($param->isOptional()) $param_list .= "[";
                    if($param->isPassedByReference()) $param_list .= "&";
                    if($param->getName() == "")
                    {
                        $param_list .= "\$param$idxp";
                        $idxp++;
                    }
                    else
                    {
                        $param_list .= "$".$param->getName();
                    }
                    if($param->isOptional()) $param_list .= "]";
                    $param_list .= ", ";
                }
                $param_list = preg_replace("/, $/", "", $param_list);
                
                $kstr = substr("     ".$k,-4);
                
                $this->logging("***    $kstr: Method $type $method($param_list)", "I_CLASS", 1);
                $k++;
            }
            
            $j++;
        }
        
    }
    
    
    
    /**
     * Log message from type PROC_OS (process and os)
     * 
     * This method initiates log of the process and operating system infos 
     * to the logfile. This log masseges are from the type PROC_OS to 
     * indicate 'process and os'. 
    **/
    public function process_os()
    {
        // get operating system infos
        $uname = php_uname('a');
        $this->logging("Operatingsystem (OS): $uname", "PROC_OS", 1);
        
        // get process-id
        $proc_id = getmypid();
        $this->logging("Process ID: $proc_id", "PROC_OS", 1);
        
        
        
        // get real process user-id
        $proc_ruid = posix_getuid();
        $proc_ruser = posix_getpwuid($proc_ruid);
        $this->logging("Process real user: ".$proc_ruser["name"]." ($proc_ruid)", "PROC_OS", 1);
        
        // get real process user-id
        $proc_euid = posix_geteuid();
        $proc_euser = posix_getpwuid($proc_euid);
        $this->logging("Process effective user: ".$proc_euser["name"]." ($proc_euid)", "PROC_OS", 1);
        
        
        
        // get real process group-id
        $proc_rgid = posix_getgid();        // real GID
        $proc_rgroup = posix_getgrgid($proc_rgid);
        $this->logging("Process real group: ".$proc_rgroup["name"]."($proc_rgid)", "PROC_OS", 1);
        
        // get effective process group-id
        $proc_egid = posix_getegid();       // effective GID
        $proc_egroup = posix_getgrgid($proc_egid);
        $this->logging("Process effective group: ".$proc_egroup["name"]."($proc_egid)", "PROC_OS", 1);
        
        
        
        // get script file user-id and name
        $script_uid = getmyuid();
        $script_user = posix_getpwuid($script_uid);
        $this->logging("Script user: ".$script_user["name"]." ($script_uid)", "PROC_OS", 1);
        
        // get script file group-id
        $script_gid = getmygid();
        $script_group = posix_getgrgid($script_gid);
        $this->logging("Script group: ".$script_group["name"]." ($script_gid)", "PROC_OS", 1);
        
    }
    
    
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // END OF METHODS
    ////////////////////////////////////////////////////////////////////////////
    
}


?>