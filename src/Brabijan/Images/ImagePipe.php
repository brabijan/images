<?php

namespace Brabijan\Images;

use Nette;

/**
 * @author Jan Brabec <brabijan@gmail.com>
 */
class ImagePipe extends Nette\Object
{

	/** @var string */
	private $assetsDir;

	/** @var string */
	private $wwwDir;

	/** @var string */
	private $path;

	/** @var string */
	private $originalPrefix = "original";

	/** @var string */
	private $baseUrl;

	/** @var string|null */
	private $namespace = NULL;



	/**
	 * @param $assetsDir
	 * @param $wwwDir
	 * @param Nette\Http\Request $httpRequest
	 */
	public function __construct($assetsDir, $wwwDir, Nette\Http\Request $httpRequest)
	{
		$this->wwwDir = $wwwDir;
		$this->assetsDir = $assetsDir;
		$this->baseUrl = rtrim($httpRequest->url->baseUrl, '/');
	}



	/**
	 * @param $path
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}



	/**
	 * @param $dir
	 */
	public function setAssetsDir($dir)
	{
		$this->assetsDir = $dir;
	}



	/**
	 * @return string
	 */
	public function getAssetsDir()
	{
		return $this->assetsDir;
	}



	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path !== NULL ? $this->path : $this->baseUrl . str_replace($this->wwwDir, '', $this->assetsDir);
	}



	/**
	 * @param $originalPrefix
	 */
	public function setOriginalPrefix($originalPrefix)
	{
		$this->originalPrefix = $originalPrefix;
	}



	/**
	 * @throws \Nette\InvalidStateException
	 */
	private function checkSettings()
	{
		if ($this->assetsDir == NULL) {
			throw new Nette\InvalidStateException("Dir is not setted");
		}
		if (!file_exists($this->assetsDir)) {
			throw new Nette\InvalidStateException("Dir does not exists");
		}
		if ($this->getPath() == NULL) {
			throw new Nette\InvalidStateException("Path is not setted");
		}
	}



	/**
	 * @param $namespace
	 * @return $this
	 */
	public function setNamespace($namespace)
	{
		if (empty($namespace)) {
			$this->namespace = NULL;
		} else {
			$this->namespace = $namespace . DIRECTORY_SEPARATOR;
		}

		return $this;
	}



	/**
	 * @param string $image
	 * @param null $size
	 * @param null $flags
	 * @param bool $strictMode
	 * @return string
	 * @throws \Nette\Latte\CompileException
	 * @throws FileNotFoundException;
	 */
	public function request($image, $size = NULL, $flags = NULL, $strictMode = FALSE)
	{
		$this->checkSettings();
		if (empty($image)) {
			return "#";
		}
		if ($size === NULL) {
			return $this->getPath() . "/" . $this->namespace . $this->originalPrefix . "/" . $image;
		}

		list($width, $height) = explode("x", $size);
		if ($flags == NULL) {
			$flags = Nette\Image::FIT;
		} elseif (!is_int($flags)) {
			switch (strtolower($flags)):
				case "fit":
					$flags = Nette\Image::FIT;
					break;
				case "fill":
					$flags = Nette\Image::FILL;
					break;
				case "exact":
					$flags = Nette\Image::EXACT;
					break;
				case "shrink_only":
					$flags = Nette\Image::SHRINK_ONLY;
					break;
				case "stretch":
					$flags = Nette\Image::STRETCH;
					break;
			endswitch;
			if (!isset($flags)) {
				throw new Nette\Latte\CompileException('Mode is not allowed');
			}
		}

		$thumbPath = "/" . $this->namespace . $flags . "_" . $width . "x" . $height . "/" . $image;
		$thumbnailFile = $this->assetsDir . $thumbPath;
		$originalFile = $this->assetsDir . "/" . $this->namespace . $this->originalPrefix . "/" . $image;

		if (!file_exists($thumbnailFile)) {
			$this->mkdir(dirname($thumbnailFile));
			if (file_exists($originalFile)) {
				$img = Nette\Image::fromFile($originalFile);
				$img->resize($width, $height, $flags);
				$img->save($thumbnailFile);
			} elseif ($strictMode) {
				throw new FileNotFoundException;
			}
		}
		$this->namespace = NULL;

		return $this->getPath() . $thumbPath;
	}



	/**
	 * @param string $dir
	 *
	 * @throws \Nette\IOException
	 * @return void
	 */
	private static function mkdir($dir)
	{
		$oldMask = umask(0);
		@mkdir($dir, 0777, TRUE);
		@chmod($dir, 0777);
		umask($oldMask);

		if (!is_dir($dir) || !is_writable($dir)) {
			throw new Nette\IOException("Please create writable directory $dir.");
		}
	}

}
