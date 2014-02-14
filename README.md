PHP-Tinned-Core
===============
The Core Module contains the basic Core functionality for the Tinned-Framework.

Class Building Block
--------------------
The Main class contained in this module provides basic debugging and error 
reporting functions to any class which inherits from it. It is the base of 
all of the classes included in any of the PHP-Tinned Framework modules, thus
making the Core Module a prerequisite of any of the Framework's other modules.

### Using the Tinned-Core with other Modules ###
Tinned Framework Modules should all be checked at the same level of the
directory hierarchy. I.e. the directory structure of the Tinned Modules on 
your system should look like this:

    /path/to/tinned/src/
    /path/to/tinned/src/log
    /path/to/tinned/src/PHP-Tinned-Core
    /path/to/tinned/src/PHP-Tinned-SQL
    /path/to/tinned/src/PHP-Tinned-xxxx
    /path/to/tinned/src/PHP-Tinned-yyyy
    etc...

The PHP-Tinned-Core must be available and placed in this fashion in the directory
structure in order for the other modules to funtion.

For log files, the log directory should be created and should be writable by the
user or process which is accessing the PHP files. 

Debugging Functionality
-----------------------
An optional, yet very powerful debugging class. This class's funtionality can
also be used independently of the framework, but is best suited for integration
with the Main class. 

### Output Format ###
A sample output line follows:

     - 0.6073 | 127.0.0.1       | DEBUG    |    69 | 1025_test_main.class.php       | ---                         | Initial Log Entry! followed by 1 second sleep.

@todo - column definitions here!

### Output Targets ###
The logger supports output to one or more of the following targets:
* log file
* to the browser (standard output)

### Customizable Output ###
The Debug class's functionality also provides the ability to assign basic logging 
levels to error messages, thus facilitating an easy way (through class 
configuration parameters) to enable or disable particular types of messages.

Types include (can all be independently enabled or disabled):
* informational messages (file loaded, class initalized, etc..)
* error reporting (error ocurred in code)
* level one debug messages (more detailed debugging information)
* level two debug messages (even more detailed information)

### Performance Metrics ###
The Debug class also provides statistics as to the performance of PHP and the
classes being debugged. This includes tracking script's execution time (and the
time elapsed between log messages) as well as memory usage, etc...

@todo more specific stuff here...
