<?php
/**
 * PDF Package
 *
 * The TJS Technology PDF Package for Fuel takes any PHP5 PDF generating class
 * as a driver and acts as a wrapper for that class, thus integrating PDF generation
 * into Fuel without reinventing the wheel.
 *
 * @package		TJS
 * @author		TJS Technology Pty Ltd
 * @copyright	Copyright (c) 2011 TJS Technology Pty Ltd
 * @license		See LICENSE
 * @link		http://www.tjstechnology.com.au
 */

namespace Pdf;

class Pdf {
	
	// Lib path
	protected $_lib_path = '';
	
	// Driver Class
	protected $_driver_class = '';
	
	// Driver Instance
	protected $_driver_instance = '';
	
	/**
	 * Construct
	 * 
	 * Called when the class is initialised
	 * 
	 * @access	protected
	 * @return	PDF\PDF
	 */
	protected function __construct($driver = null)
	{
		// Load Config
		\Config::load('pdf', true);
		
		// Default Driver
		if ($driver == null)
		{
			$driver = \Config::get('pdf.default_driver');
		}
		
		// Set the lib path
		$this->set_lib_path(PKGPATH . 'pdf' . DS . 'lib' . DS);
		
		$drivers = \Config::get('pdf.drivers');
		$temp_driver = (isset($drivers[$driver])) ? $drivers[$driver] : false;
		
		if ($temp_driver === false)
		{
			throw new \Exception(sprintf('Driver \'%s\' doesn\'t exist.', $driver));
		}
		
		$driver = $temp_driver;
		
		// Include files
		foreach ($driver['includes'] as $include)
		{
			include_once($this->_get_include_file($include));
		}
		
		$this->set_driver_class($driver['class']);
		
		// Return this object. User must now call init and provide the parameters that
		// the driver wants. This action is caught by __call()
		return $this;
	}
	
	/**
	 * Get Include File
	 * 
	 * Gets the path of the include file and
	 * makes it safe for Windows users.
	 * 
	 * @access	protected
	 * @param	string	file location (relative to lib path)
	 * @return	string	real file location
	 */
	protected function _get_include_file($file)
	{
		$file = sprintf('%s%s', $this->get_lib_path(), str_replace('/', DS, $file));
		
		if ( ! file_exists($file))
		{
			throw new \Exception(sprintf('File \'%s\' doesn\'t exist.', $file));
		}
		
		return $file;
	}
	
	/**
	 * Factory
	 * 
	 * Creates new instance of class
	 * 
	 * @access	public
	 * @return	PDF\PDF
	 */
	public static function factory($driver = null)
	{
		return new PDF($driver);
	}
	
	/**
	 * Camel to Underscore
	 * 
	 * Translates a camel case string into a string with underscores (e.g. firstName -> first_name)
	 * 
	 * @access	public
	 * @param	string	Camel-cased string
	 * @return	string	Underscored string
	 */
	public function camel_to_underscore($string)
	{
		$string[0]	= strtolower($string[0]);
		$function	= create_function('$c', 'return "_" . strtolower($c[1]);');
		
		return preg_replace_callback('/([A-Z])/', $function, $string);
	}
	
	/**
	 * Underscore to Camel
	 * 
	 * Translates a string with underscores into camel case (e.g. first_name -> firstName)
	 * 
	 * @access	public
	 * @param	string	Camel-cased string
	 * @param	bool	Pascal-case (firstName -> FirstName)
	 * @return	string	Underscored string
	 */
	public function underscore_to_camel($string, $pascal_case = false)
	{
		// Do we want to pascal-case it?
		if ($pascal_case)
		{
			$string[0] = strtoupper($string[0]);
		}
		
		$function = create_function('$c', 'return strtoupper($c[1]);');
		
		return preg_replace_callback('/_([a-z])/', $function, $string);
	}
	
	/**
	 * Call
	 * 
	 * Magic method to catch all calls
	 * 
	 * @access	public
	 * @param	string	method
	 * @param	array	arguments
	 * @return	mixed
	 */
	public function __call($method, $arguments)
	{
		// Init
		if ($method == 'init')
		{
			// Get new instance and provide arguments
			$reflect = new \ReflectionClass($this->get_driver_class());
			$instance = $reflect->newInstanceArgs($arguments);
			
			$this->set_driver_instance($instance);
			
			return $this;
		}
		
		// Get cameled method
		$cameled_method = $this->underscore_to_camel($method);
		
		if (method_exists($this->_driver_instance, $method))
		{
			$pdf = $this->get_driver_instance();
			
			$return = call_user_func_array(array($pdf, $method), $arguments);
			return ($return) ? $return : $this;
		}
		else if (method_exists($this->_driver_instance, $cameled_method))
		{
			$pdf = $this->get_driver_instance();
			
			$return = call_user_func_array(array($pdf, $cameled_method), $arguments);
			return ($return) ? $return : $this;
		}
		
		// Generic getter / setter
		
		// check if method is not public (protected methods called
		// outside are routed here)
		if (method_exists($this, $method))
		{
			$reflection = new ReflectionMethod($this, $name);
			
			if ( ! $reflection->isPublic())
			{
				throw new \Exception(sprintf('Call to non-public method %s::%s() caught by %s', $name, get_called_class(), get_called_class()));
			}
		}
		
		// Method (set / get)
		$method_type = substr($method, 0, 3);
		
		// Variable to set or get
		$variable = substr($method, 4);
		$protected_variable = '_' . $variable;
		
		// Verbose mode
		// The 'true' parameter might move depending on if
		// we're setting something
		if ($method_type === 'get')
		{
			$verbose = (isset($arguments[0])) ? $arguments[0] : false;
		}
		else if ($method_type === 'set')
		{
			$verbose = (isset($arguments[1])) ? $arguments[1] : false;
		}
		
		// Value
		if ($method_type === 'set')
		{
			$value = (isset($arguments[0])) ? $arguments[0] : false;
		}
		
		// See if it's a get or set
		if ($method_type === 'get' || $method_type === 'set')
		{
			if (isset($this->$variable))
			{
				if ($method === 'get')
				{
					return $this->$variable;
				}
				else if ($method === 'set')
				{
					$this->$variable = $value;
					
					return $this;
				}
			}
			// else check for that variable with an underscore first
			// (used in protected variables) - get_test() will first
			// check for $this->test, and then if non-existent check
			// for $this->_test
			else if (isset($this->$protected_variable))
			{
				if ($method_type === 'get')
				{
					return $this->$protected_variable;
				}
				else if ($method_type === 'set')
				{
					$this->$protected_variable = $value;
					
					return $this;
				}
			}
			else
			{
				if ($verbose)
				{
					throw new \Exception(sprintf('Variable $%s does not exist in class %s', $variable, get_called_class()));
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			throw new \Exception(sprintf('Call to undefined method %s::%s()', get_called_class(), $name));
		}
	}
}