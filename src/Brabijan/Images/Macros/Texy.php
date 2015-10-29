<?php

namespace Brabijan\Images\Macros;

use Brabijan\Images\FileNotFoundException;
use Brabijan\Images\ImagePipe;
use Nette;

class Texy extends Nette\Object
{
	/**
	 * @param \Texy|\Texy\Texy $texy
	 * @param ImagePipe $imagePipe
	 */
	public static function register($texy, ImagePipe $imagePipe)
	{
		if (!($texy instanceof \Texy) && !($texy instanceof \Texy\Texy)) {
			throw new \InvalidArgumentException('The $texy parameter is not instance of Texy or Texy\Texy');
		}

		$texy->addHandler("image", function ($invocation, $image, $link) use ($imagePipe) {
			if (!($invocation instanceof \TexyHandlerInvocation) && !($invocation instanceof \Texy\HandlerInvocation)) {
				throw new \InvalidArgumentException('The $invocation parameter is not instance of TexyHandlerInvocation or Texy\HandlerInvocation');
			}
			if (!($image instanceof \TexyImage) && !($image instanceof \Texy\Image)) {
				throw new \InvalidArgumentException('The $image parameter is not instance of TexyImage or Texy\Image');
			}

			$arguments = Helpers::prepareMacroArguments($image->URL);
			try {
				$image->URL = $imagePipe->setNamespace($arguments["namespace"])->request($arguments["name"], $arguments["size"], $arguments["flags"], TRUE);
			} catch (FileNotFoundException $e) {
				$image->URL = $arguments["name"];
				if (!empty($arguments["size"])) {
					list($image->width, $image->height) = explode("x", $arguments["size"]);
				}
			}

			return $invocation->proceed($image, $link);
		});
	}

}