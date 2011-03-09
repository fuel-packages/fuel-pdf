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

namespace PDF;

use \TJS\Object as Object;

class PDF extends Object
{
	// Lib path
	protected $_lib_path = '';
	
	// Drivers
	protected $_drivers = array(
		'tcpdf'		=> array(
			'includes'	=> array(
				// Relative to lib path
				'tcpdf/config/lang/eng.php',
				'tcpdf/tcpdf.php',
			),
			'class'		=> 'TCPDF',
		),
		'dompdf'	=> array(
			'includes'	=> array(
				'dompdf/dompdf_config.inc.php',
			),
			'class'		=> 'DOMPDF',
		),
	);
	
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
		// Set the lib path
		$this->set_lib_path(PKGPATH . 'pdf' . DS . 'lib' . DS);
		
		$drivers = $this->get_drivers();
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
	 * Get TCPDF
	 * 
	 * Gets the TCPDF object
	 *
	 * @access	public
	 * @return	\TCPDF
	 */
	public function get_tcpdf()
	{
		return ( ! empty($this->_tcpdf)) ? $this->_tcpdf : false;
	}
	
	/**
	 * Get TCPDF
	 * 
	 * Gets the TCPDF object
	 *
	 * @access	public
	 * @return	PDF\PDF
	 */
	public function set_tcpdf(\TCPDF $pdf)
	{
		$this->_tcpdf = $pdf;
		
		return $this;
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
		
		// Try to assign to TCPDF class
		$cameled_method = $this->underscore_to_camel($method);
		
		// print_r(get_class_methods($this->_driver_instance));
		
		if (method_exists($this->_driver_instance, $cameled_method))
		{
			$pdf = $this->get_driver_instance();
			
			return call_user_func_array(array($pdf, $cameled_method), $arguments);
		}
		
		return parent::__call($method, $arguments);
	}
}