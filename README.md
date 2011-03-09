##Developers
* Ben Corlett (PHP Developer, TJS Technology) [@ben_corlett](http://twitter.com/ben_corlett)
* Thomas Stevens (Lead Developer, TJS Technology) [@tomo89aus](http://twitter.com/tomo89aus)

##To install this package

* Through OIL:
	php oil install pdf

* Manually:
	1. [Download](https://github.com/TJS-Technology/fuel-pdf/zipball/master) or [Clone](https://github.com/TJS-Technology/fuel-pdf) the repo.

##How it works

The TJS Technology PDF class is a driver-based PDF class. Why reinvent the wheel when you can adapt well-developed and maintained code?
You can add most of any PHP5 PDF generating libraries under the lib folder and configure it in the config files.

The PDF class then forwards any functions you call onto the parent class (you call functions using Fuel's standards - underscores as opposed to camcel-casing or pascal casing).
Even if the PDF library you use takes camel-casing, you can just use underscores and this package adapts and changes your function calls so they work with the library.

##Usage
To use, firstly call the static method factory() on the PDF class, and provide it one string - the driver to use (by default there are two drivers - dompdf and tcpdf).
Then chain on the init() method and provide any parameters that the driver's library needs when calling a new instance. DomPDF doesn't take any input however tcpdf does (see the code under lib/tcpdf or the examples for the parameters to provide).

DomPDF example:
		// Create an instance of the PDF class
		$pdf = \PDF::factory('dompdf')->init();

TCPDF example:
		// Create an instance of the PDF class
		// Construct takes following input: $orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false
		$pdf = \PDF::factory('tcpdf')->init('P', 'mm', 'A4', true, 'UTF-8', false);
		// Of course these parameters are optional so we could just call init(). All errors are handled by the libraries themselves.

Now once you've initialised the class you use the libraries exactly as documented. See [TCPDF](http://www.tcpdf.org/) and [DomPDF](http://code.google.com/p/dompdf/) documentation.

* Note: As explained earlier, you can camelcase functions used in the PDF classes so that it feels more Fuel-like (is that a word??).
		// Normally, to add a page in TCPDF you call addPage();
		$pdf = \PDF::factory('tcpdf')->init('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->add_page(); // This works.

##Adding Libraries
Adding libraries is stupidly simple.
* Download the PHP5 PDF library.
* Drop the folder it's contained in under the lib/ directory of this package.
* You'll need to add the following code into the PDF config file (which you should have copied to your APPPATH/config/ directory):
		// Look for the following code
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
		
		// Add a new driver to the array
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
			
			// New Driver
			'somenewpdfdriver' => array(
				'includes'		=> array(
					'somenewpdfdriver/somenewpdfdriver.php',
				),
				'class'			=> 'somenewpdfdriver'
			),
		),
* Now, simply when you initialise the pdf class:
		$pdf = \PDF::factory('somenewpdfdriver')->init('option1', 'anotheroption');