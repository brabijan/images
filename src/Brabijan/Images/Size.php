<?php

namespace Brabijan\Images;

use Nette;

/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @property-read float|int $width
 * @property-read float|int $height
 */
class Size extends Nette\Object {

	/** @var float|int */
	private $width;

	/** @var float|int */
	private $height;


	/**
	 * @param float|int $width
	 * @param float|int $height
	 */
	public function __construct($width, $height) {
		$this->width = $width;
		$this->height = $height;
	}


	/**
	 * @return float|int
	 */
	public function getHeight() {
		return $this->height;
	}


	/**
	 * @return float|int
	 */
	public function getWidth() {
		return $this->width;
	}


	/**
	 * @param string $file
	 * @return Size
	 */
	public static function fromFile($file) {
		list($width, $height) = @getimagesize($file);
		return new Size($width, $height);
	}

}