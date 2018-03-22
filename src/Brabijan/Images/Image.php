<?php

namespace Brabijan\Images;

use Nette;

/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @property-read string $file
 * @property-read Size $size
 */
class Image
{

	use Nette\SmartObject;

	/** @var string */
	private $file;

	/** @var Size */
	private $size;



	/**
	 * @param string $file
	 */
	public function __construct($file)
	{
		$this->file = $file;
		$this->size = Size::fromFile($file);
	}



	/**
	 * @return bool
	 */
	public function exists()
	{
		return file_exists($this->file);
	}



	/**
	 * @return float|int
	 */
	public function getFile()
	{
		return $this->file;
	}



	/**
	 * @return string
	 */
	public function getBasename()
	{
		return basename($this->getFile());
	}



	/**
	 * @return Size
	 */
	public function getSize()
	{
		return $this->size;
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getBasename();
	}

}