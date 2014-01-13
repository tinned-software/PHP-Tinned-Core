<?php
/**
 * @author Tyler ASHTON ( tyler [.] ashton [at] tinned-software [.] net 
 * @copyright Copyright (c) 2010 - 2014
 * @version 1.5.2
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * @package framework
 * @subpackage xml
 *
 * XML Manager class file
 *
 * The XML Manager class provides a PHP native interface to working with XML
 * documents. It accepts a number of input formats including XML from a file,
 * a string containing XML, or a PHP array and can convert between these formats. 
 * 
 * This class is built using the built in Document Object Model parsing 
 * package of PHP , and therefore requires that these classes be available to 
 * function. The XMLWriter package is required to convert a PHP array back 
 * into a XML text document.
 * 
 * @link http://www.php.net/manual/en/book.dom.php
 * @link http://www.php.net/manual/en/book.xmlwriter.php
 * @todo implement namespaces, comments and other node types 
 *
**/


/**
 * Include required files
**/
include_once(dirname(__FILE__).'/main.class.php');



/**
 * A class for conversion of XML data between text and PHP formats.
 * 
 * This class provides an easy interface to convert XML data between different
 * input formats. The class can work with XML data as a string, from a file,
 * or represented as a recursive, associative array in PHP. When data is loaded 
 * in one format the class can convert between the formats i.e. a properly 
 * formed XML string can be loaded be loaded and converted to a PHP array,  
 * maniuplated by a PHP script and then converted back to a XML string. 
 * 
 * Possible input formats for XML input are: <br/>
 * File on the filesystem<br/>
 * PHP String<br/>
 * PHP associative array<br/>
 * 
 * The PHP associative array for an element "name" with an attribute "type":
 * &lt;name type=real&gt;80798&lt;/name&gt;
 * 
 * is displayed as:
 * <pre>[Zip] => Array
 * (
 *     [@attributes] => Array
 *         (
 *             [type] => real
 *         )
 *     [@value] => 80798
 * )</pre>
 * 
 * and for two sibling elements "zip" with values:
 * &lt;zip&gt;80798&lt;/zip&gt;&lt;zip&gt;43017&lt;/zip&gt;
 * 
 * is displayed as:
 * <pre>[Zip] => Array
 * (
 *     [0] => 80798
 *     [1] => 43017
 * )</pre>
 * 
 * 
 * 
 * ERROR CODES <br/>
 * 100 .................... Invalid XML string: XML not well formed<br/>
 * 101 .................... String contains false encoding<br/>
 * 102 .................... File contains false encoding<br/>
 * 103 .................... DTD invalid<br/>
 * 104 .................... DOM DTD validation failed<br/>
 * 105 .................... Required classes or functions are not available<br/>
 * 106 .................... Array does not conform to numbered_elements setting!<br/>
 * 
 * @package framework
 * @subpackage xml
 * 
**/
class XML_Manager extends Main
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
    
    // used to store a XMLWriter class and used by different public / private
    // methods of the class
    private $xmlwriter_object = NULL;
    
    //PHP DOM instance to process parse XML
    private $dom_object = NULL;
    
    // Configuration item to extend each element with a numeric index
    private $numbered_elements = FALSE;
    
    // Verify the loaded document against the specified DTD
    private $dtd_validate = FALSE;
    
    // Internal variable to store the DOCTYPE of the document.
    private $document_type = NULL;
    
    // Force class to only accept ASCII or UTF-8 character input via arrays or strings
    private $strict_encoding_bool = TRUE;
    private $strict_encoding_param = NULL;
    
    // variables to contain xml version and encoding loaded from xmlstring
    private $xml_document_encoding = NULL;
    private $xml_document_version = NULL;
    
    // switchs to enable / disable specific functions
    private $disable_mb_functions = FALSE;
    
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
        
        $required_functions = array('iconv','mb_detect_encoding');
        $required_classes = array('DOMDocument', 'XMLWriter');
        $check_prerequisites = parent::check_prerequisites($required_functions, $required_classes);
        
        // set the error variable
        $error = ($check_prerequisites !== TRUE) ? TRUE : FALSE;
        
        //
        // check if missing functions can be disabled in the class
        // if so we disable them and set the error back to FALSE
        
        // mb_* functions can be skipped
        if($check_prerequisites !== TRUE && in_array('mb_detect_encoding', $check_prerequisites['functions']))
        {
            parent::info('disable mb_* functions!');
            $this->disable_mb_functions = TRUE;
            $error = FALSE;
        }
        
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
     * Set configured value for always numbered elements.
     *
     * Numbered elements forces that every element has a numeric index of zero
     * even if the element is an only child, i.e. has no sibling elements. See
     * example below:
     * <pre>
     *     Non Numbered         ||||||||||||           Numbered
     *
     *  [price] => Array        <= versus =>  [price] =>
     *  (                       <= versus =>  (
     *      [@value] => 49.99   <= versus =>      [0] =>  Array
     *  )                       <= versus =>      (
     *                          <= versus =>          [@value] => 49.99
     *                          <= versus =>      )
     *                          <= versus =>  )
     * </pre>
     * 
     * Default value for this setting is FALSE.
     * 
     * @access public
     * @see get_numbered_elements()
     * 
     * @param boolean $enable
    **/
    public function set_numbered_elements($enable)
    {
        if(is_bool($enable) === FALSE)
        {
            parent::error('parameter must be boolean!');
            return FALSE;
        }
        $this->numbered_elements = $enable;
    }
    
    
    
    /**
     * Set configured value for DTD verification.
     * 
     * When enabled the class will check any XML document in the class against
     * the DTD which is specified in the document. If no DTD is specified the
     * document can not and will not be verified.
     * 
     * Default value for this setting is FALSE.
     * 
     * @access public
     * @see get_dtd_validate()
     * 
     * @param boolean $enable
    **/
    public function set_dtd_validate($enable)
    {
        if(is_bool($enable) === FALSE)
        {
            parent::error('parameter must be boolean!');
            return FALSE;
        }
        $this->dtd_validate = $enable;
    }
    
    
    
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
     * the specified charcater set to UTF-8 if the input character set.
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
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////
    // GET methods to get class Options
    ////////////////////////////////////////////////////////////////////////////
    
    
    
    /**
     * Get configured value for always numbered elements.
     * 
     * @access public
     * @see set_numbered_elements()
     * 
     * @return boolean numbered_elements
    **/
    public function get_numbered_elements()
    {
        return $this->numbered_elements;
    }
    
    
    
    /**
     * Get configured value for DTD verification.
     * 
     * @access public
     * @see set_dtd_validate()
     * 
     * @return boolean dtd_validate
    **/    
    public function get_dtd_validate()
    {
        return $this->dtd_validate;
    }
    
    
    
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
                $this->errtxt = 'Invalid character encoding in XML';
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
                $this->errtxt = 'Invalid character encoding in XML';
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
     * A method to load a XML document as a string into a DOMDocument object
     * 
     * This method takes the given XML string and attempts to load it into the
     * DOMDocument Object which is stored internally in the class. Upon success
     * TRUE is returned or on failure FALSE - if the method fails the failure
     * reason can be read using the get_last_error() method.
     * 
     * @access private
     * @see get_last_error()
     * 
     * @param string $xmlstring the input string to check
     * @return boolean whether load to the DOM object was successful or not
    **/
    private function _load_string_to_dom($xmlstring)
    {
        // if strict encoding is turned off the string must be converted from the
        // character set specified when strict encoding was turned off into utf-8
        parent::debug2('xml encoding '.$this->xml_document_encoding);
        parent::debug2('xml version '.$this->xml_document_version);
        parent::debug2('class strict_encoding '.($this->strict_encoding_bool ? 'true' : 'false'));
        
        // if strict encoding has been disable and there was no encoding parameter
        // contained in the xml definiton element then use the encoding which was
        // specified when strict encoding was disabled to translate the string to utf-8
        if($this->strict_encoding_bool === FALSE)
        {
            if(isset($this->xml_document_encoding) === FALSE)
            {
                $this->_convert_encoding($xmlstring);
            }
        }
        // DOMDocument will issue a warning if string is blank ("")
        if(isset($xmlstring) === TRUE && $xmlstring != "")
        {
            $this->dom_object = new DOMDocument;
            
            // load the string into the DOM object
            if($this->dom_object->loadXML($xmlstring) == FALSE)
            {
                $this->errnr = 100;
                $this->errtxt = 'Invalid XML string: XML not well formed';
                parent::error("DOM object creation returned FALSE, XML string is not well formed");
                return FALSE;
            }
            return TRUE;
        }
        else
        {
            $this->errnr = 100;
            $this->errtxt = 'Invalid XML string: empty string';
            parent::error("XML string is empty");
            return FALSE;
        }
    }
    
    
    
    /**
     * Convert xml-array to xml-node (xml string)
     * 
     * This function is called in a recursive fashion, increasing the 'level'
     * parameter each time so the function knows how deep it is in the array
     * and can process different levels with different criteria.
     * 
     * @access private
     * @param array $array_param the array to process
     * @param integer $level the level of recusivitiy in which the function is called
    **/
    private function _convert_array_to_node($array_param = NULL, $level = 0)
    {
        // define a log prefix to avoid PHP_ERR warnings
        $log_prefix = NULL;
        
        if($this->dbg_level > 0)
        {
            // a killswitch to prevent an infinite loop
            if($level == 100)
            {
                die("max recursive limit reached, breaking\n");
            }
            
            $log_prefix = '';
            for($i = 0; $i < $level; $i++)
            {
                $log_prefix .= ' ';
            }
        }
        parent::debug2($log_prefix . "(array depth is: $level)");
        
        if(is_array($array_param))
        {
            foreach($array_param as $key => $value)
            {
                //
                // process the first level (xml root) array
                //
                if($level == 0)
                {
                    //
                    // process special tags
                    //
                    if(preg_match('/^@/', $key))
                    {
                        //
                        // process DTD tags
                        //
                        parent::debug2($log_prefix . "(found @ parameter '$key')");
                        if(preg_match('/@DTD/', $key))
                        {
                            if(isset($value['name']) === FALSE || isset($value['type']) === FALSE || isset($value['dtd']) === FALSE)
                            {
                                parent::debug($log_prefix . "required DTD array paremeters are missing, not processing DTD!");
                                continue;
                            }
                            
                            // the DOMDocument - DOMDocumentType element determines whether
                            // a document type is PUBLIC or SYSTEM based solely on whether the
                            // parameter publicId is passed to the startDTD() method. If the
                            // publicId is NULL the DOMDocumentType is output as SYSTEM, if the
                            // publicId contains a string the DOMDocumentType is PUBLIC.
                            if($value['type'] == 'SYSTEM')
                            {
                                // SYSTEM doctype does not have an identifier
                                $value['identifier'] = NULL;
                            }
                            elseif($value['type'] == 'PUBLIC')
                            {
                                if(isset($value['identifier']) === FALSE)
                                {
                                    parent::debug($log_prefix . "PUBLIC DTD set but no public identifier set in array!");
                                    $this->errnr = 103;
                                    $this->errtxt = 'Invalid DTD: PUBLIC DTD set but no public identifier set in array!';
                                    continue;
                                }
                            }
                            elseif($value['type'] == 'INLINE')
                            {
                                parent::debug($log_prefix . "Invalid DTD: INLINE DTD set but not supported!");
                                $this->errnr = 103;
                                $this->errtxt = 'Invalid DTD: INLINE DTD set but not supported!';
                                continue;
                            }
                            else
                            {
                                parent::debug($log_prefix . "DTD type {$value['type']} unrecognized, skipping!");
                                $this->errnr = 103;
                                $this->errtxt = "DTD type {$value['type']} unrecognized, skipping!";
                                continue;
                            }
                            
                            // set variables
                            $this->_convert_encoding($value['name']);
                            $this->_convert_encoding($value['identifier']);
                            $this->_convert_encoding($value['dtd']);
                            $name = $value['name'];
                            $publicId = $value['identifier'];
                            $systemId = $value['dtd'];
                            
                            parent::debug2('DTD found!' ." name: '{$value['name']}', type: '{$value['type']}', identifier: '{$value['identifier']}' dtd: '{$value['dtd']}'");
                            $this->xmlwriter_object->startDTD($name, $publicId, $systemId);
                            if(isset($value['elements']) === TRUE)
                            {
                                foreach($value['elements'] as $dtd_element_key => $dtd_element_value)
                                {
                                    // @todo implement here and in xmlstring_to_array
                                }
                            }
                            $this->xmlwriter_object->endDTD();
                            continue;
                        }
                        
                    }
                    //
                    // process level zero array elements not beginning with @
                    //
                    else
                    {
                        // process normal root tags tags
                        parent::debug2($log_prefix . "startElement($key)");
                        $this->_convert_encoding($key);
                        $this->xmlwriter_object->startElement($key);
                        
                        if(isset($value['@attributes']))
                        {
                            foreach($value['@attributes'] as $attr_key => $attr_value)
                            {
                                $this->_convert_encoding($attr_value, $attr_key);
                                parent::debug2($log_prefix . "writeAttribute($attr_key, $attr_value)");
                                $this->xmlwriter_object->writeAttribute($attr_key, $attr_value);
                            }
                        }
                        
                        // parent::debug2($log_prefix . "processElement($key)");
                        
                        // call the function increasing the level
                        $this->_convert_array_to_node($value, $level + 1);
                        
                        parent::debug2($log_prefix . "endElement() [$key]");
                        $this->xmlwriter_object->endElement();
                    }
                }
                //
                // process subsequent, non level 0 arrays
                //
                else
                {
                    // skip already processed attributes element
                    if($level == 1 && preg_match('/^@attributes/', $key))
                    {
                        parent::debug2($log_prefix . 'skipping already processed root level @attributes element');
                        continue;
                    }
                    
                    if(preg_match('/^@/', $key) === 0 && $this->numbered_elements === TRUE && isset($value[0]) === FALSE)
                    {
                        if(is_null($value[0]) === FALSE)
                        {
                            $this->errnr = 106;
                            $this->errtxt = "Array element '$key' does not conform to numbered_elements setting!";
                            parent::debug("Array element '$key' does not conform to numbered_elements setting!");
                            return FALSE;
                        }
                    }
                    
                    // array for internal processing
                    $value_internal = array();
                    
                    // check numbered_elements class setting and when necessary convert
                    // non conforming arrays to numerically indexed ones
                    // @see set_numbered_elements()
                    if(isset($value[0]) === FALSE && $this->numbered_elements == FALSE)
                    {
                        if(preg_match('/^@/', $key) === 0)
                        {
                            parent::debug2($log_prefix . "(converting non zero array per class setting)");
                            $int_array = $value;
                            $value_internal[0] = $int_array;
                        }
                        else
                        {
                            $value_internal = $value;
                        }
                    }
                    else
                    {
                        $value_internal = $value;
                    }
                    
                    
                    // at this point all arrays conform to the numbered_elements
                    // specification
                    // @see set_numbered_elements()
                    
                    // count the number of elements
                    $element_count = count($value_internal);
                    
                    parent::debug2($log_prefix . "(process non root array '$key' w/ '$element_count') " . ($element_count == 1 ? 'value' : 'values'));
                    
                    // process each element
                    for($i = 0; $i < $element_count; $i++)
                    {
                        // look for element names without leading @
                        if(preg_match('/^@/', $key) === 0)
                        {
                            $this->_convert_encoding($key);
                            parent::debug2($log_prefix . "startElement($key)");
                            $this->xmlwriter_object->startElement($key);
                        }
                        
                        // parent::debug2($log_prefix . "(array : )".print_r($value_internal,true));
                        
                        // process node attributes
                        if(isset($value_internal[$i]['@attributes']))
                        {
                            foreach($value_internal[$i]['@attributes'] as $attribute_name => $attribute_value)
                            {
                                $this->_convert_encoding($attribute_value, $attribute_name);
                                parent::debug2($log_prefix . "writeAttribute({$attribute_name}, {$attribute_value})");
                                $this->xmlwriter_object->writeAttribute($attribute_name, $attribute_value);
                            }
                        }
                        
                        // process node value (text)
                        if(isset($value_internal[$i]['@value']))
                        {
                            $this->_convert_encoding($value_internal[$i]['@value']);
                            parent::debug2($log_prefix . "text({$value_internal[$i]['@value']})");
                            $this->xmlwriter_object->text($value_internal[$i]['@value']);
                            // $data_read = TRUE;
                        }
                        
                        // check for sub nodes which need to be processed recursively
                        if(isset($value_internal[$i]) && is_array($value_internal[$i]))
                        {
                            $this->_convert_array_to_node($value_internal[$i], $level + 1);
                        }
                        
                        // end the element
                        if(preg_match('/^@/', $key) === 0)
                        {
                            parent::debug2($log_prefix . "endElement() [$key]");
                            $this->xmlwriter_object->endElement();
                        }
                    }
                }
            }
        }
        else
        {
            parent::error('function called with a non array parameter!');
            return FALSE;
        }
    }
    
    
    
    /**
     * 
     * @access private
     * @param $dom_node DOMNode a DOMNode object to process
     * @param $level integer the level of recusivitiy in which the function is called
    **/
    function _convert_node_to_array($dom_node = FALSE, $level = 0)
    {
        // does the current DOMNode object contain attributes?
        $attributes = FALSE;
        // is the current node a DTD node?
        $dtd_node = FALSE;
        // result to return from the function
        $result = NULL;
        // define a log prefix to avoid PHP_ERR warnings
        $log_prefix = NULL;
        
        if($this->dbg_level > 0)
        {
            // a killswitch to prevent an infinite loop
            if($level == 100)
            {
                die("max recursive limit reached, breaking\n");
            }
            
            $log_prefix = '';
            for($i = 0; $i < $level; $i++)
            {
                $log_prefix .= ' ';
            }
        }
        
        parent::debug2($log_prefix . "DOMNode level is '{$level}'");
        
        // Check the nodeType of the DOMNode to see if it is implemented by the function
        switch($dom_node->nodeType)
        {
            case '1':
                // found a ELEMENT_NODE - check for node attributes
                // ONLY ELEMENT_NODEs will have attributes
                if($dom_node->hasAttributes() === TRUE)
                {
                    $attributes = TRUE;
                    parent::debug2($log_prefix . "node has attributes, will process them");
                }
            case '2':
                // found a ATTRIBUTE_NODE
            case '3':
                // found a TEXT_NODE
                break;
                
            case '10':
                // found a found a DOCUMENT_TYPE_NODE
                parent::debug2($log_prefix . "marking dtd_node");
                $dtd_node = TRUE;
                
                break;
                
            // found a element types after this line are not handled by the function so
            // found a therefore they display a notification message are:
            case '4':
                // found a CDATA_SECTION_NODE
            case '5':
                // found a ENTITY_REFERENCE_NODE
            case '6':
                // found a ENTITY_NODE
            case '7':
                // found a PROCESSING_INSTRUCTION_NODE
            case '8':
                // found a COMMENT_NODE
            case '9':
                // found a DOCUMENT_NODE
            case '11':
                // found a DOCUMENT_FRAGMENT_NODE
            case '12':
                // found a NOTATION_NODE
            default:
                parent::info("cannot process parameter name:{$dom_node->nodeName} type:{$dom_node->nodeType}");
                return FALSE;
        }
        
        // if attributes were found process them
        if($attributes === TRUE)
        {
            $temp_attributes = $dom_node->attributes;
            // found attributes - processing each attribute (a DOMNode Object)
            // using a foreach loop.
            foreach($temp_attributes as $attribute)
            {
                // if the element has a named prefix add this
                // i.e. xmlns in: <x xmlns:edi='http://ecommerce.org/schema'>
                $named_prefix = ($attribute->prefix) ? $attribute->prefix.':' : '';
                parent::debug2($log_prefix . "attribute name: {$attribute->nodeName} value: {$attribute->nodeValue} -- ");
                $result['@attributes'][$named_prefix.$attribute->nodeName] = $attribute->nodeValue;
            }
        }
        
        // if a dtd node was found process it and return it as an array
        if($dtd_node === TRUE)
        {
            parent::debug2($log_prefix . "dtd publicId: '{$dom_node->publicId}'");
            parent::debug2($log_prefix . "dtd systemId: '{$dom_node->systemId}'");
            parent::debug2($log_prefix . "dtd name: '{$dom_node->name}'");
            
            // define the type of DTD
            // valid types are SYSTEM, PUBLIC, or INLINE 
            $type = 'SYSTEM';
            $identifier = NULL;
            
            // parent::debug($log_prefix . 'type:' . gettype($dom_node->publicId) . ' length:' . strlen($dom_node->publicId));
            if(strlen($dom_node->publicId) !== 0)
            {
                $type = 'PUBLIC';
                $identifier = $dom_node->publicId;
            }
            //if(FALSE)
            //{
            // INLINE DTD not yet implemented
            //}
            
            return array('type' => $type, 'name' => $dom_node->name, 'identifier' => $identifier, 'dtd' => $dom_node->systemId);
            
            // properties of this type of node are:
            // readonly public string $publicId ;
            // readonly public string $systemId ;
            // readonly public string $name ;
            // readonly public DOMNamedNodeMap $entities ;
            // readonly public DOMNamedNodeMap $notations ;
            // readonly public string $internalSubset ;
        }
        
        parent::debug2($log_prefix . "name:{$dom_node->nodeName} type:{$dom_node->nodeType}");
        
        // if first child is not null the element has children.
        $children_node_list = $dom_node->childNodes;
        
        // process each child node which was found
        for($i = 0; $i < $children_node_list->length; $i++)
        {
            // now we process each child element of the node list first reading it
            // into a temporary variable DOMNode Object which is then evaluated based
            // what type of node it is.
            $temp_node = $children_node_list->item($i);
            
            // test to see if there are multiple elements with the same name, if there
            // are add them to a numerically indexed array
            $multiple_nodes_with_same_name = FALSE;
            foreach($children_node_list as $child)
            {
                // check node names, if they are equal, check to make sure the node is not the same DOMNode object
                if($temp_node->nodeName == $child->nodeName && $child->isSameNode($temp_node) === FALSE)
                {
                    // if names are the same and DOMNode is NOT the same we have multiple DOMNodes with the same name
                    $multiple_nodes_with_same_name = TRUE;
                    break;
                }
            }
            
            // 
            // BEGIN PROCESSING DIFFERENT NODE TYPES
            // 
            // execute code for text nodes 
            if($temp_node->nodeType == XML_TEXT_NODE)
            {
                parent::debug2($log_prefix . "name:{$temp_node->nodeName} type:{$temp_node->nodeType} class:" . get_class($temp_node) . " content:" . str_replace("\n", '\\n', $temp_node->nodeValue) . " / at ". ($i + 1) ." of {$children_node_list->length}");
                // test to see if the element has only whitespace or not
                // i.e. <name>x</name> evaluates to FALSE while <name><b>x</></name> to TRUE for the name element
                if($temp_node->isWhitespaceInElementContent() === FALSE)
                {
                    $result['@value'] = $temp_node->nodeValue;
                }
                
                continue;
            }
            // execute code for remaining NON element nodes to break the loop 
            // if a element node is found we must process the node recursively
            elseif($temp_node->nodeType != XML_ELEMENT_NODE)
            {
                // parent::debug2($log_prefix . " name:{$temp_node->nodeName} type:{$temp_node->nodeType} / at ". ($i + 1) ." of {$children_node_list->length}");
                continue;
            }
            
            // done processing non XML_ELEMENT_NODEs and if we have an XML_ELEMENT_NODE
            // call this function recursively to process the full depth of the DOM Object
            $child_node = $temp_node;
            
            $named_prefix = ($temp_node->prefix) ? $temp_node->prefix.':' : '';
            
            if($this->numbered_elements == TRUE)
            {
                // if multiple nodes with the same name were found create array with
                // numeric indexs
                if($multiple_nodes_with_same_name)
                {
                    $result[$named_prefix.$temp_node->nodeName][] = $this->_convert_node_to_array($child_node, $level + 1);
                }
                else
                {
                    $result[$named_prefix.$temp_node->nodeName][0] = $this->_convert_node_to_array($child_node, $level + 1);
                }
            }
            else
            {
                // if multiple nodes with the same name were found create array with
                // numeric indexs
                if($multiple_nodes_with_same_name)
                {
                    $result[$named_prefix.$temp_node->nodeName][] = $this->_convert_node_to_array($child_node, $level + 1);
                }
                else
                {
                    $result[$named_prefix.$temp_node->nodeName] = $this->_convert_node_to_array($child_node, $level + 1);
                }
            }
        }
        return $result;
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
     * This method takes a properly formed string which was loaded via one of
     * the load_(string,file) methods and loads/parses it using the PHP Document
     * Object Model (DOM) libraries. It converts a xml string to an array with 
     * no further calls needed if the xml string is not yet defined in the class
     * the parameter passed to this function is used
     * 
     * @access public
     * 
     * @see _convert_node_to_array()
     * @param string $xmlstring a well formed XML string to be converted to an associative array
     * @return array associative array representing the XML-data or FALSE if no string is set
    **/
    public function xmlstring_to_array($xmlstring)
    {
        // check if an error has occured
        if($this->errnr !== NULL)
        {
            parent::error('Error in class, returning FALSE');
            return FALSE;
        }
        
        // check to see that the parameter is a valid string
        if(is_string($xmlstring) === FALSE)
        {
            parent::error('parameter is not a string, returning FALSE');
            return FALSE;
        }
        
        // get version from the loaded string
        $temp_array = array();
        preg_match("/version\=['\"]([^'\"]+)['\"]/", $xmlstring, $temp_array);
        if(isset($temp_array[1]))
        {
            $this->xml_document_version = $temp_array[1];
            parent::debug2("version extracted from xmlstring is '{$temp_array[1]}'");
            unset($temp_array);
        }
        else
        {
            parent::debug2("no version found in xml string");
        }
        
        // get encoding from the loaded string
        $temp_array = array();
        preg_match("/encoding\=['\"]([^'\"]+)['\"]/", $xmlstring, $temp_array);
        if(isset($temp_array[1]))
        {
            $this->xml_document_encoding = $temp_array[1];
            parent::debug2("encoding extracted from xmlstring is '{$temp_array[1]}'");
            unset($temp_array);
        }
        else
        {
            parent::debug2("no encoding found in xmlstring");
        }
        
        // check string encoding
        // error number / text set in the _convert_encoding method 
        if($this->_check_encoding($xmlstring) === FALSE)
        {
            parent::error('invalid encoding in string, returning FALSE');
            return FALSE;
        }
        
        // call the internal method to load the string to a DOMDocument object
        if($this->_load_string_to_dom($xmlstring) === FALSE)
        {
            return FALSE;
        }
        
        $return_array = array();
        
        // by definition the first child should be a Document Type Declaration,
        // so we load the first child and thencheck to see if it is actually
        // a DTD - if it is not we simply ignore it and process it as a normal
        // node 
        $dom_node = $this->dom_object->firstChild;
        
        // the DTD element must be processed first if it is found (it must always
        // be the firstChild by XML document definition
        if($dom_node->nodeType == '10')
        {
            $this->document_type = $this->_convert_node_to_array($dom_node);
            parent::debug2("processing DOCUMENT_TYPE_NODE\n".print_r($this->document_type, TRUE));
            $return_array['@DTD'] = $this->document_type;
        }
        
        // get the first node and loop until we find the name of the root
        // node (the first node is the Document Type Declaration and the
        // first named one (with ->tagName) will be the root node
        if(isset($dom_node->tagName) === FALSE)
        {
            while(isset($dom_node->tagName) === FALSE)
            {
                $dom_node = $dom_node->nextSibling;
                if(isset($dom_node->tagName))
                {
                    parent::debug2("name of root DOM node is {$dom_node->tagName}");
                    $root_node_name = $dom_node->tagName;
                    break;
                }
            }
        }
        else
        {
            $root_node_name = $dom_node->tagName;
        }
        
        $return_array[$root_node_name] = $this->_convert_node_to_array($dom_node);
        
        if($this->dtd_validate === TRUE)
        {
            parent::debug('validating DOM against DTD');
            $result = $this->dom_object->validate();
            
            if($result === FALSE)
            {
                parent::debug('validating DOM against DTD, check failed, return FALSE!!');
                $this->errnr = 104;
                $this->errtxt = 'DOM DTD validation failed';
                return FALSE;
            }
        }
        else
        {
            if($this->errnr !== NULL)
            {
                parent::debug('an error occured while processing the XML return FALSE!');
                return FALSE;
            }
        }
        
        return $return_array;
    }
    
    
    
    /**
     * Convert a properly formatted PHP associative array to a XML string.
     * 
     * This method converts the array which is stored in the object into a
     * properly formed XML string. The method does the conversion with no 
     * further calls needed.
     * 
     * @access public
     * 
     * @see _convert_array_to_node()
     * @param bool $array the array to convert to a XML string
     * @return string returns a well formed XML string
    **/
    public function array_to_xmlstring($array = NULL)
    {
        // check to see if an error has occured
        if($this->errnr !== NULL)
        {
            parent::error('error in class, conversion not successful!');
            return FALSE;
        }
        
        // check to see that the parameter is a valid array
        if(is_array($array) === FALSE)
        {
            parent::error('function called with a non array parameter!');
            return FALSE;
        }
        
        $this->xmlwriter_object = new XMLWriter();
        
        // run appropriate XMLWriter initilaizaion procedures which must be
        // executed before attempting to write XML elements with _convert_array_to_node()
        $this->xmlwriter_object->openMemory();
        $this->xmlwriter_object->startDocument('1.0','UTF-8');
        $this->xmlwriter_object->setIndent(TRUE);
        $this->xmlwriter_object->setIndentString('    ');
        
        // todo integrate DOCTYPE functionality
        
        // parent::debug2(print_r($array,true));
        
        $this->_convert_array_to_node($array);
        
        // check for an internal error caused during 
        if($this->errnr === NULL)
        {
            $this->xmlwriter_object->endDocument();
            
            $this->errnr = NULL;
            $this->errtxt = NULL;
            
            $xml_string = $this->xmlwriter_object->outputMemory();
            
            if($this->dtd_validate === TRUE)
            {
                parent::debug('validating DOM against DTD');
                
                // load the constructed string to the DOMDocument object so we can check it for validity
                if($this->_load_string_to_dom($xml_string) === FALSE)
                {
                    parent::error('xml_string generated by the class is invalid!');
                    return FALSE;
                }
                
                // run the validate() method of DOMDocument to check the document for validity
                if($this->dom_object->validate() === FALSE)
                {
                    parent::debug('validating DOM against DTD, check failed, return FALSE!!');
                    $this->errnr = 104;
                    $this->errtxt = 'DOM DTD validation failed';
                    return FALSE;
                }
            }
            
            return $xml_string;
        }
        else
        {
            parent::error('internal error in class, returning FALSE');
            return FALSE;
        }
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