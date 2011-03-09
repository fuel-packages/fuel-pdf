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
		//Test
		$testcode->asdf();