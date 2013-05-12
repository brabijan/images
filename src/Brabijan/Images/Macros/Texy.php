<?php

namespace Brabijan\Images\Macros;

use Brabijan\Images\ImagePipe;
use Nette;

class Texy extends Nette\Object
{

	public static function register(\Texy $texy, ImagePipe $imagePipe)
	{
		$texy->allowed["brabijan/images"] = TRUE;
		$texy->registerBlockPattern(function ($parser, $matches, $name) use ($imagePipe) {
			$arguments = rtrim(ltrim($matches[0], '[img '), ']');
			$arguments = Helpers::prepareMacroArguments($arguments);
			$el = \TexyHtml::el("img");
			$el->src = $imagePipe->setNamespace($arguments["namespace"])->request($arguments["name"], $arguments["size"], $arguments["flags"]);

			return $el;
		}, "#\[img ([a-z0-9/.-]*)(, ?([0-9]*)x([0-9]*)(, ?[a-z]*)?)?\]#m", "brabijan/images");
	}

}