<?php
/**
 * TJS Framework
 *
 * TJS Framework standard classes for building web applications.
 *
 * @package		TJS
 * @author		Ben Corlett
 * @copyright	Copyright (c) 2011 TJS Technology Pty Ltd
 * @license		See LICENSE
 * @link		http://www.tjstechnology.com.au
 */

Autoloader::add_core_namespace('Pdf');

Autoloader::add_classes(array(
	'Pdf\\Pdf'						=> __DIR__ . '/classes/pdf.php',
));


/* End of file bootstrap.php */