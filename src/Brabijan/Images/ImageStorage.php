<?php

namespace Brabijan\Images;

use Nette;
use Nette\Http\FileUpload;
use Nette\Utils\Finder;
use Nette\Utils\Strings;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ImageStorage extends Nette\Object
{

	/** @var string */
	private $imagesDir;

	/** @var string */
	private $namespace = NULL;

	private $originalPrefix = "original";



	/**
	 * @param string $dir
	 */
	public function __construct($dir)
	{
		if (!is_dir($dir)) {
			umask(0);
			mkdir($dir, 0777);
		}
		$this->imagesDir = $dir;
	}



	/**
	 * @param $originalPrefix
	 */
	public function setOriginalPrefix($originalPrefix)
	{
		$this->originalPrefix = $originalPrefix;
	}



	/**
	 * @param $namespace
	 * @return $this
	 */
	public function setNamespace($namespace)
	{
		if ($namespace === NULL) {
			$this->namespace = NULL;
		} else {
			$this->namespace = $namespace . DIRECTORY_SEPARATOR;
		}

		return $this;
	}



	/**
	 * @param $dir
	 */
	public function setImagesDir($dir)
	{
		if (!is_dir($dir)) {
			umask(0);
			mkdir($dir, 0777);
		}
		$this->imagesDir = $dir;
	}



	/**
	 * @param FileUpload $file
	 * @return Image
	 * @throws \Nette\InvalidArgumentException
	 */
	public function upload(FileUpload $file)
	{
		if (!$file->isOk() || !$file->isImage()) {
			throw new Nette\InvalidArgumentException;
		}

		do {
			$name = Strings::random(10) . '.' . $file->getSanitizedName();
		} while (file_exists($path = $this->imagesDir . DIRECTORY_SEPARATOR . $this->namespace . $this->originalPrefix . DIRECTORY_SEPARATOR . $name));

		$file->move($path);
		$this->namespace = NULL;

		return new Image($path);
	}



	/**
	 * @param string $content
	 * @param string $filename
	 * @return Image
	 */
	public function save($content, $filename)
	{
		do {
			$name = Strings::random(10) . '.' . $filename;
		} while (file_exists($path = $this->imagesDir . DIRECTORY_SEPARATOR . $name));

		file_put_contents($path, $content);

		return new Image($path);
	}



	/**
	 * @return string
	 */
	public function getImagesDir()
	{
		return $this->imagesDir;
	}



	/**
	 * @param $param
	 * @throws FileNotFoundException
	 * @return string
	 */
	public function find($param)
	{
		foreach (Finder::findFiles($param)->from($this->imagesDir) as $file) {
			/** @var \SplFileInfo $file */
			return $file->getPathname();
		}

		throw new FileNotFoundException("File $param not found.");
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FileNotFoundException extends \RuntimeException
{

}