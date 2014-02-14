<?php
/**
 * @author Tyler ASHTON ( tyler [.] ashton [at] tinned-software [.] net 
 * @copyright Copyright (c) 2010 - 2014
 * @version 1.1.1
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * @package framework
 * @subpackage json
 *
 * JSON Manager class file
 *
 * The JSON Manager class provides a PHP native interface to working with JSON
 * documents. It accepts a number of input formats including JSON
 * a string containing JSON, or a PHP array and can convert between these formats. 
 *
**/



/**
 * Include required files
**/
include_once(dirname(__FILE__).'/main.class.php');
if(function_exists('json_encode') === FALSE) include_once(dirname(__FILE__).'/../functions/json_encode.php');
if(function_exists('json_decode') === FALSE) include_once(dirname(__FILE__).'/../functions/json_decode.php');



/**
 * A class for conversion of JSON data between text and PHP formats.
 * 
 * This class provides an easy interface to convert JSON data between different
 * input formats. The class can work with JSON data as a string, from a file,
 * or represented as a recursive, associative array in PHP. When data is loaded 
 * in one format the class can convert between the formats i.e. a properly 
 * formed JSON string can be loaded be loaded and converted to a PHP array,  
 * maniuplated by a PHP script and then converted back to a JSON string. 
 * 
 * Possible input formats for JSON input are: <br/>
 * PHP String<br/>
 * PHP associative array<br/>
 * 
 * ERROR CODES <br/>
 * 101 .................... Invalid character encoding in JSON!<br/>
 * 102 .................... JSON string could not be converted!<br/>
 * 105 .................... Internal Error / required classes or functions missing!<br/>
 * 
 * @package framework
 * @subpackage json
 * 
**/
class JSON_Manager extends Main
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
    
    // Configuration item to extend each element with a numeric index
    // private $numbered_elements = FALSE;
    
    // Force class to only accept ASCII or UTF-8 character input via arrays or strings
    private $strict_encoding_bool = TRUE;
    private $strict_encoding_param = NULL;
    
    // switchs to enable / disable specific functions
    private $disable_mb_functions = FALSE;
    
    // return StdClass (TRUE) or assoc. array (FALSE)
    private $return_assoc_array = TRUE;
    
    // enable or disable compatibility mode
    private $compatibility_mode = TRUE;
    
    // Variables to hold last error code and text
    private $errnr = NULL;
    private $errtxt = NULL;
    
    
    ////////////////////////////////////////////////////////////////////////////
    // CONSTRUCTOR & DESCTRUCTOR methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    /**
     * Constructor for the class
     * 
     * @access public
     * 
     * @param $dbg_level Debug log level
     * @param &$debug_object Debug object to send log messages to
    **/
    public function __construct ($dbg_level = 0, &$debug_object = NULL)
    {
        date_default_timezone_set("UTC");
        
        // initialize parent class MainClass
        parent::Main_init($dbg_level, $debug_object);
        
        $this->dbg_level  = $dbg_level;
        $this->log_object = $debug_object;
        
        $required_functions = array('json_encode', 'json_decode');
        $required_classes = array();
        $check_prerequisites = parent::check_prerequisites($required_functions, $required_classes);
        
        // set the error variable
        $error = ($check_prerequisites !== TRUE) ? TRUE : FALSE;
        
        // mb_* functions can be skipped
        if($check_prerequisites !== TRUE && in_array('mb_detect_encoding', $check_prerequisites['functions']))
        {
            parent::info('disable mb_* functions!');
            $this->disable_mb_functions = TRUE;
            $error = FALSE;
        }
        
        //
        // check if missing functions can be disabled in the class
        // if so we disable them and set the error back to FALSE
        
        if($error === TRUE)
        {
            $this->errnr = 105;
            $this->errtxt = 'Internal Error / required classes or functions missing';
            return FALSE;
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // SET methods to set class Options
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Set whether the class should use compatibility mode for array conversion
     *
     * Enable or disable compability mode for the PHP internal representation of  
     * JSON strings. Compatibility mode means that every JSON value ( "key":"the value" )
     * will be represented as array('key' => array('@value' => 'the value')) in PHP.
     * 
     * @access public
     * @see get_compatibility_mode()
     * 
     * @param boolean $enable
    **/
    public function set_compatibility_mode($enable)
    {
        if(is_bool($enable) === FALSE)
        {
            parent::error('parameter must be boolean!');
            return FALSE;
        }
        parent::debug2('set '.__FUNCTION__.' to '.($enable ? 'true' : 'false'));
        $this->compatibility_mode = $enable;
    }
    
    
    
    /**
     * Set whether the class returns a StdClass object or an associative array
     *
     * Default class setting is to return an associative array. Otherwise the 
     * class will return an object of the type StdClass.
     * 
     * @access public
     * @see get_return_array()
     * 
     * @param boolean $enable
    **/
    public function set_return_array($enable)
    {
        if(is_bool($enable) === FALSE)
        {
            parent::error('parameter must be boolean!');
            return FALSE;
        }
        parent::debug2('set '.__FUNCTION__.' to '.($enable ? 'true' : 'false'));
        $this->return_assoc_array = $enable;
    }
    
    
    
    // *
    //  * Set configured value for always numbered elements.
    //  *
    //  * Numbered elements forces that every element has a numeric index of zero
    //  * even if the element is an only child, i.e. has no sibling elements. See
    //  * example below:
    //  * <pre>
    //  *     Non Numbered         ||||||||||||           Numbered
    //  *
    //  *  [price] => 49.99        <= versus =>  [price] =>
    //  *                          <= versus =>  (
    //  *                          <= versus =>      [0] =>  49.99
    //  *                          <= versus =>  )
    //  * </pre>
    //  * 
    //  * Default value for this setting is FALSE.
    //  * 
    //  * @access public
    //  * @see get_numbered_elements()
    //  * 
    //  * @param boolean $enable
    // *
    /*
    public function set_numbered_elements($enable)
    {
        if(is_bool($enable) === FALSE)
        {
            parent::error('parameter must be boolean!');
            return FALSE;
        }
        parent::debug2('set '.__FUNCTION__.' to '.($enable ? 'true' : 'false'));
        $this->numbered_elements = $enable;
    }
    */
    
    
    /**
     * Set configuration value to for strict encoding checks 
     * 
     * If strict encoding is enabled (default is ENABLED) the class will not 
     * accept any input (strings or arrays) which is not UTF-8 or ASCII conform,
     * instead returning false and setting an error in the class. If strict 
     * checking is disabled all characters which fall outside of the ASCII 
     * range will be assumed to be in the specified character set and converted 
     * to the corresponding UTF-8 character.
     * 
     * NOTE: disabling this check will attempt to force UTF-8 encoding.
     * Please keep in mind that these functions will ONLY successfully convert 
     * the specified charcater set to UTF-8 if the input encoding is set.
     * 
     * Default value for this setting is TRUE.
     * 
     * @access public
     * @see get_strict_encoding()
     * 
     * @param boolean $enable
     * @param string $encoding
    **/
    public function set_strict_encoding($enable, $encoding = NULL)
    {
        if(is_bool($enable) === FALSE)
        {
            parent::error('parameter must be boolean!');
            return FALSE;
        }
        if($enable === FALSE)
        {
            if(is_null($encoding))
            {
                parent::info('cannot disable strict encoding without specifying a character set!');
            }
            else
            {
                $this->strict_encoding_param = $encoding;
                $this->strict_encoding_bool = $enable;
            }
        }
        else
        {
            $this->strict_encoding_bool = $enable;
        }
        parent::debug2('set '.__FUNCTION__.' to '.($enable ? 'true' : 'false'));
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // GET methods to get class Options
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Get configured value for php json.
     * 
     * @access public
     * @see set_compatibility_mode()
     * 
     * @return boolean php json
    **/
    public function get_compatibility_mode()
    {
        return $this->compatibility_mode;
    }
    
    
    
    /**
     * Get configured value for std class
     * 
     * @access public
     * @see set_return_array()
     * 
     * @return boolean numbered_elements
    **/
    public function get_return_array()
    {
        return $this->return_assoc_array;
    }
    
    
    
    /**
     * Get configured value for always numbered elements.
     * 
     * @access public
     * @see set_numbered_elements()
     * 
     * @return boolean numbered_elements
    **/
    /*
    public function get_numbered_elements()
    {
        return $this->numbered_elements;
    }
    */
    
    
    /**
     * Get configuration value to for strict encoding checks 
     * 
     * @access public
     * @see set_strict_encoding()
     * 
     * @return boolean strict_encoding
    **/
    public function get_strict_encoding()
    {
        return $this->strict_encoding_bool;
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // PRIVATE methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Checks a string for correct encoding 
     * 
     * This function uses the mb_detect_encoding function to determine whether
     * the given string conforms to the accepted encodings UTF8 or ASCII. If the
     * function detects an error in the encoding it returns FALSE as well as 
     * setting the class private error variables to the appropriate error
     * text and message.
     * 
     * @access private
     * 
     * @param string $string the input string to check
     * @param string $key the input key to check (for use in a 'foreach $value => $key' loop)
     * @return boolean representing whether input complies to the accepted encodings
    **/
    private function _check_encoding($string, $key = NULL)
    {
        parent::debug2("Function called _check_encoding, parameter:".$string." and ".$key);
        
        // if mb functions are disabled return true
        if($this->disable_mb_functions === TRUE)
        {
            return TRUE;
        }
        // mb_detect_encoding does not correctly detect cyrillic character sets
        // @see http://bugs.php.net/bug.php?id=38138
        $skip_encodings = array('windows-1251', 'cp866', 'koi8-r');
        foreach($skip_encodings as $skipped)
        {
            $skip = FALSE;
            // parent::debug2("$skipped to {$this->xml_document_encoding}");
            if(isset($this->xml_document_encoding) && preg_match("/$skipped/i", $this->xml_document_encoding))
            {
                $skip = TRUE;
            }
            // parent::debug2("$skipped to {$this->strict_encoding_param}");
            if(isset($this->strict_encoding_param) && preg_match("/$skipped/i", $this->strict_encoding_param))
            {
                $skip = TRUE;
            }
            if($skip === TRUE)
            {
                parent::info("character set '$skipped' detected, skipping mb_detect_encoding() check!");
                return TRUE;
            }
        }
        
        
        // array containing valid encodings, in order of priority. Valid charsets are:
        // UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP 
        $valid_encoding_array = array('ASCII', 'UTF-8');
        
        
        
        // if strict encoding is disabled the user set a custom encoding which
        // should also be checked
        if($this->strict_encoding_bool === FALSE)
        {
            $valid_encoding_array[] = $this->strict_encoding_param;
        }
        elseif(isset($this->xml_document_encoding))
        {
            $valid_encoding_array[] = $this->xml_document_encoding;
        }
        
        
        parent::debug2("Checking encoding '$key => $string'");
        
        // mb_detect_encoding returns encoding string from array above
        // if the string is valid, FALSE otherwise
        $valid_encoding = mb_detect_encoding("$key => $string", $valid_encoding_array, TRUE);
        
        if($valid_encoding === FALSE)
        {
            parent::error('loaded string does not conform to ' . implode(', ', $valid_encoding_array) . '!');
            if($this->strict_encoding_bool === TRUE)
            {
                $this->errnr = 101;
                $this->errtxt = 'Invalid character encoding in JSON';
            }
            return FALSE;
        }
        else
        {
            parent::debug2("Detected encoding is $valid_encoding");
            return TRUE;
        }
    }
    
    
    
    /**
     * Converts a string to specified encoding 
     * 
     * This function determines whether the given string conforms to the 
     * valid encodings UTF8 or ASCII. If it does and strict checking is disabled
     * in the class the strings will be converted to the encoding specified.
     * If strict checking is enabled this function returns FALSE.
     * 
     * @access private
     * 
     * @param string &$string the input string to check
     * @param string &$key the input key to check (for use in a 'foreach $value => $key' loop)
     * @return boolean representing whether input complies to the accepted encodings
    **/
    private function _convert_encoding(&$string, &$key = NULL)
    {
        if($this->disable_mb_functions === FALSE)
        {
            // array containing valid encodings, in order of priority
            $valid_encoding_array = array('ASCII', 'UTF-8');
            // returns encoding string from array above if the string is valid, FALSE otherwise
            // parent::debug2("checking encoding '\$key => \$string'");
            parent::debug2("Checking encoding '$key => $string'");
            $valid_encoding = mb_detect_encoding("$key => $string", $valid_encoding_array, TRUE);
        }
        else
        {
            $valid_encoding = 'UTF-8';
        }
        
        if($valid_encoding === FALSE)
        {
            if($this->strict_encoding_bool === TRUE)
            {
                parent::error('loaded string does not conform to ASCII or UTF-8!');
                $this->errnr = 101;
                $this->errtxt = 'Invalid character encoding in JSON';
                return FALSE;
            }
            else
            {
                parent::debug2("{$this->strict_encoding_param}, 'UTF-8', \$key");
                $key_enc = iconv($this->strict_encoding_param, 'UTF-8', $key);
                $string_enc = iconv($this->strict_encoding_param, 'UTF-8', $string);
                parent::debug2("key '$key' encoded to '" . $key_enc);
                parent::debug2("string '$string' encoded to '" . $string_enc);
                $key = $key_enc;
                $string = $string_enc;
                unset($key_enc, $string_enc);
            }
        }
        else
        {
            parent::debug2("Detected encoding is $valid_encoding");
            return TRUE;
        }
    }
    
    
    
    /**
     * A function to encode a regular array into a format which is compatible with
     * XML Manager 1.x 
     * 
     * Enable or disable compability mode for the PHP internal representation of  
     * JSON strings. Compatibility mode means that every JSON value ( "key":"the value" )
     * will be represented as array('key' => array('@value' => 'the value')) in PHP.
     * 
     * @param $input_array array the array to encode
     **/
    private function _encode_compatibility($input_array, $level = 0, $count = 0)
    {
        // define a variable to indicate if an attribute is being processed
        static $attribute = FALSE;
        static $current_attribute = 1;
        
        $log_prefix = '';
        for($i = 0; $i < $level; $i++)
        {
            $log_prefix .= ' ';
        }
        
        foreach($input_array as $key => $value)
        {
            parent::debug2("{$log_prefix}encoding level $level: $key");
            if($key === '@attributes')
            {
                $attribute = TRUE;
                parent::debug2("{$log_prefix}found attr at $level: $key");
            }
            if(is_array($value))
            {
                $input_array[$key] = $this->_encode_compatibility($value, $i + 1, count($value));
            }
            else
            {
                
            }
            if(is_array($value) == FALSE)
            {
                parent::debug2("{$log_prefix} processing '$key' value:$value ".(($attribute === TRUE) ? "attribute:$current_attribute of $count" : NULL));
                if($attribute)
                {
                    $input_array[$key] = $value;
                    if($current_attribute == $count)
                    {
                        $attribute = FALSE;
                        $current_attribute = 1;
                    }
                    else
                    {
                        $current_attribute++;
                    }
                }
                else
                {
                    $input_array[$key] = array('@value' => $value);
                }
            }
        }
        return $input_array;
    }
    
    
    
    /**
     * A function to decode a regular array into a format which is compatible with
     * XML Manager 1.x 
     * 
     * Enable or disable compability mode for the PHP internal representation of  
     * JSON strings. Compatibility mode means that every JSON value ( "key":"the value" )
     * will be represented as array('key' => array('@value' => 'the value')) in PHP.
     * 
     * @param $input_array array the array to decode
     **/
    private function _decode_compatibility($input_array, $level = 0)
    {
        $log_prefix = '';
        for($i = 0; $i < $level; $i++)
        {
            $log_prefix .= ' ';
        }
        
        foreach($input_array as $key => $value)
        {
            parent::debug2("{$log_prefix}decoding level $level: $key");
            if(is_array($value) == TRUE  && isset($value['@value']) === FALSE)
            {
                $input_array[$key] = $this->_decode_compatibility($value, $i + 1);
            }
            if(is_array($value) == TRUE)
            {
                if(isset($value['@value']) === TRUE)
                {
                    $new_value = $value['@value'];
                    parent::debug2("{$log_prefix} processing '$key' value: $new_value");
                    $input_array[$key] = $new_value;
                }
                elseif(key($value) === '@value')
                {
                    parent::debug2("{$log_prefix} processing '$key' value: NULL ".key($value));
                    $input_array[$key] = NULL;
                }
            }
        }
        return $input_array;
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // PROTECTED methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // PUBLIC methods of the class
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Convert the loaded string or file into an associative array.
     * 
     * This method takes a properly formed string and loads/parses it using the 
     * PHP function json_decode(). It converts a JSON string directly to an array 
     * 
     * @access public
     * 
     * @param string $jsonstring a well formed XML string to be converted to an associative array
     * @return array associative array representing the XML-data or FALSE if no string is set
    **/
    public function jsonstring_to_array($jsonstring)
    {
        // check if an error has occured
        if($this->errnr !== NULL)
        {
            parent::error('Error in class, returning FALSE');
            return FALSE;
        }
        
        // check to see that the parameter is a valid string
        if(is_string($jsonstring) === FALSE)
        {
            parent::error('parameter is not a string, returning FALSE');
            return FALSE;
        }
        
        // check string encoding
        // error number / text set in the _convert_encoding method 
        if($this->_check_encoding($jsonstring) === FALSE)
        {
            parent::error('invalid encoding in string, returning FALSE');
            return FALSE;
        }
        
        $return_array = json_decode($jsonstring, $this->return_assoc_array);
        
        if($return_array === NULL)
        {
            $this->errnr = 102;
            $this->errtxt = 'JSON string could not be converted!';
            
            parent::debug('result of _json_decode was not an array');
            return FALSE;
        }
        
        if($this->compatibility_mode === TRUE)
        {
            // convert array to the format with ['@value'] 
            $return_array = $this->_encode_compatibility($return_array);
        }
        
        // conversion was not successful
        
        return $return_array;
    }
    
    
    
    /**
     * Convert a properly formatted PHP associative array to a XML string.
     * 
     * This method converts the array which is stored in the object into a
     * properly formed JSON string via the php internal function json_encode()
     * 
     * @access public
     * 
     * @see _convert_array_to_node()
     * @param bool $array_param the array to convert to a JSON string
     * @return string returns a well formed JSON string
    **/
    public function array_to_jsonstring($array_param = NULL)
    {
        // check to see if an error has occured
        if($this->errnr !== NULL)
        {
            parent::error('error in class, conversion not successful!');
            return FALSE;
        }
        
        // check to see that the parameter is a valid array
        if(is_array($array_param) === FALSE)
        {
            parent::error('function called with a non array parameter!');
            return FALSE;
        }
        
        if($this->compatibility_mode === TRUE)
        {
            // convert array to the format without ['@value'] 
            $array_param = $this->_decode_compatibility($array_param);
        }
        
        $string = json_encode($array_param);
        
        return $string;
    }
    
    
    
    /**
     * Gets the last error raised by error handling of the class
     *
     * Returns the last error raised in the class - valid types are
     * errnr and errtxt returning an integer with the error number or
     * error text as a string, respectively.
     * 
     * @access public
     * @param string $type "errnr" for error number, "errtxt" for errortext
     * @return mixed the requested error type if an error occured or FALSE if there was no error.
    **/
    public function get_last_error($type = 'errnr')
    {
        if(isset($this->errnr) === TRUE)
        {
            parent::debug2("get_error: number = {$this->errnr}, text = {$this->errtxt}");
            if($type == 'errtxt')
            {
                return $this->errtxt;
            }
            else
            {
                return $this->errnr;
            }
        }
        else
        {
            parent::debug2('method called but no error is set in the class');
            return FALSE;
        }
    }
    
    
    
    /**
     * Resets the last error raised by error handling of the class
     * 
     * Call this method to reset any errors which were raised in the class
     * 
     * @access public
    **/
    public function reset_last_error()
    {
        parent::debug2('resetting class errors');
        $this->errnr = NULL;
        $this->errtxt = NULL;
    }
    
    
    
}
?>