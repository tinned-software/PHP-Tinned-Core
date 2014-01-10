<?php
/**
 * @author Gerhard Steinbeis (info [at] tinned-software [dot] net)
 * @copyright Copyright (c) 2008 - 2013
 * @version 0.24.0
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * @package framework
 * @subpackage debug
 * 
 * Debug_Logging class interface file
 * 
**/

/**
 * Declare the interface of a db class to be used in the debug_logging class
 * classes to be used for logging in the debug_logging class must implement 
 * this interface vie "implements debug_logging_db"
**/
interface debug_logging_db
{
    public function query($string, &$error_nr, &$error_description);
}


?>