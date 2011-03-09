<?php
/**
 * TJS Framework
 *
 * TJS Framework standard classes for building web applications.
 *
 * @package		TJS
 * @author		TJS Technology
 * @copyright	Copyright (c) 2011 TJS Technology Pty Ltd
 * @license		See LICENSE
 * @link		http://www.tjstechnology.com.au
 */
return array(
	'default_driver'	=> 'dompdf',
	'drivers'			=> array(
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
	),
);