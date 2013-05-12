<?php

namespace Brabijan\Images\Macros;

use Brabijan\Images\FileNotFoundException;
use Brabijan\Images\ImagePipe;
use Nette;

class Texy extends Nette\Object
{

	public static function register(\Texy $texy, ImagePipe $imagePipe)
	{
		$texy->addHandler("image", function (\TexyHandlerInvocation $invocation, \TexyImage $image, $link) use ($imagePipe) {
			$arguments = Helpers::prepareMacroArguments($image->URL);
			try {
				$image->URL = $imagePipe->setNamespace($arguments["namespace"])->request($arguments["name"], $arguments["size"], $arguments["flags"], TRUE);
			} catch (FileNotFoundException $e) {
				$image->URL = $arguments["name"];
				if(!empty($arguments["size"])) {
					list($image->width, $image->height) = explode("x", $arguments["size"]);
				}
			}

			return $invocation->proceed($image, $link);
		});
	}

}