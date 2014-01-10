<?php
/**
 * @author Gerhard Steinbeis (info [at] tinned-software [dot] net)
 * @copyright Copyright (c) 2010
 * @version 0.1
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * @package framework
 * @subpackage TEMPLATE
 *
 * @todo template todo item
 * 
 * TEMPLATE
 *
 * This class TEMPLATE ...
 *
 *
**/


/**
 * Include required files
**/
include_once(dirname(__FILE__).'/main.class.php');



/**
 * TEMPLATE class to provide ... Short description
 * 
 * The Debug_Logging class provides ... loooong description of the class which 
 * can be multiple lines as well as multiple paragraphs.
 * 
 * Details bout this template can be found here. <br>
 * 1.) Every method or property must be documentet with the access info <br>
 * 2.) Private properties can be doccumented with single comment lines <br>
 * 3.) Every method must be created as public, protected or private <br>
 * 4.) Private methods must be named starting with "_" <br>
 * 5.) For every public "set_" method a similar named "get_ method should exist <br>
 * 6.) The methods should be seperated with 3 empty lines (indented as usual) <br>
 * 7.) The different groups of methods should be seperated as shown in the example <br>
 * 
 * All the document blocks should be written in a way that they can be parsed 
 * by the PHPDocumentor.
 * 
 * @package framework
 * @subpackage TEMPLATE
 * 
**/
class TEMPLATE extends Main
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
    public $dbg_intern = 0;
    
    
    // Variables to hold last error code and text
    private $errnr = NULL;
    private $errtxt = NULL;
    
    // private variables of the class
    
    
    ////////////////////////////////////////////////////////////////////////////
    // CONSTRUCTOR & DESCTRUCTOR methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Constructor for the class
     * 
     * This method is used to create the class. It takes the debug level and 
     * the debug object as parameter for logging.
     * 
     * @access public
     * 
     * @param $dbg_level Debug log level
     * @param $debug_object Debug object to send log messages to
    **/
    public function __construct ($dbg_level = 0, &$debug_object = null)
    {
        // initialize parent class MainClass
        parent::Main_init($dbg_level, $debug_object);
        $this->dbg_level = $dbg_level;
        $this->debug_object = &$debug_object;
        
        date_default_timezone_set("UTC");
        
        // Check Prerequisites for this class
        $required_functions = array('function1', 'function2', 'function3');
        $required_classes = array('class1');
        $check_prerequisites = parent::check_prerequisites($required_functions, $required_classes);
        
        if(($check_prerequisites !== TRUE))
        {
            $this->errnr = 105;
            $this->errtxt = 'Internal Error / required classes or functions missing';
            return FALSE;
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // SET methods to set class Options
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // GET methods to get class Options
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // PRIVATE methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // PROTECTED methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // PUBLIC methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    /**
     * Short description
     * 
     * This method is used to ... Loooong description of the method
     * 
     * @link http://www.php.net/manual/en/function.debug-backtrace.php debug_backtrace()
     * 
     * @access public
     * 
     * @param string $param1 Parameter 1 for ...
     * @param int $param2 Parameter 2 for ...
    **/
    public function TEMPLATE_DEMO($param1, $param2 = 1)
    {
        parent::debug2("Called method ".__FUNCTION__." with ... param1=".$param1.' and param2='.$param2 );
    }
    
    
    
}



?>