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
			$this->namespace = $namespace . "/";
		}

		return $this;
	}



	/**
	 * @param string $namespace
	 * @return bool
	 */
	public function isNamespaceExists($namespace) {
		return file_exists($this->imagesDir . "/" . $namespace);
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
		} while (file_exists($path = $this->imagesDir . "/" . $this->namespace . $this->originalPrefix . "/" . $name));

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
		} while (file_exists($path = $this->imagesDir . "/" . $name));

		file_put_contents($path, $content);

		return new Image($path);
	}



	/**
	 *
	 */
	public function deleteFile($filename)
	{
		if (empty($filename)) {
			throw new Nette\InvalidStateException('Filename was not provided');
		}
		/** @var $file \SplFileInfo */
		foreach (Finder::findFiles($filename)->from($this->imagesDir . ($this->namespace ? "/" . $this->namespace : "")) as $file) {
			@unlink($file->getPathname());
		}
		$this->namespace = NULL;
	}



	/**
	 * @return string
	 */
	public function getImagesDir()
	{
		return $this->imagesDir;
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FileNotFoundException extends \RuntimeException
{

}