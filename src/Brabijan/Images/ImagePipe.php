<?php

namespace Brabijan\Images;

use Nette;
use Nette\Utils\Image as NImage;

/**
 * @author Jan Brabec <brabijan@gmail.com>
 */
class ImagePipe extends Nette\Object
{

	/** @var string */
	protected $assetsDir;

	/** @var string */
	private $wwwDir;

	/** @var string */
	private $path;

	/** @var string */
	private $originalPrefix = "original";

	/** @var string */
	private $baseUrl;

	/** @var string|null */
	protected $namespace = NULL;

	/** @var array */
	public $onBeforeSaveThumbnail = array();



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
	 * @return string
	 */
	public function getOriginalPrefix()
	{
		return $this->originalPrefix;
	}



	/**
	 * @throws \Nette\InvalidStateException
	 */
	private function checkSettings()
	{
		if ($this->assetsDir == NULL) {
			throw new Nette\InvalidStateException("Assets directory is not setted");
		}
		if (!file_exists($this->assetsDir)) {
			throw new Nette\InvalidStateException("Assets directory '{$this->assetsDir}' does not exists");
		}
		elseif (!is_writeable($this->assetsDir)) {
			throw new Nette\InvalidStateException("Make assets directory '{$this->assetsDir}' writeable");
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
			$this->namespace = $namespace . "/";
		}

		return $this;
	}

	/**
         * @var string
         * @return string
         */
        public function getFromAbsolutePath($image)
        {
                $relativePath = str_replace($this->originalPrefix . '/', '', str_replace($this->assetsDir . '/', '', $image), $count);

                if ($count === 2) {
                        $relativePath = $this->originalPrefix . '/' . $relativePath;
                }

                $array = array_reverse(explode('/', $relativePath));

                $name = $array[0];
                $namespace = isset($array[1]) ? $array[1] : $array[0];

                $this->setNamespace($namespace);

                return $name;
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
		if ($image instanceof ImageProvider) {
			$this->setNamespace($image->getNamespace());
			$image = $image->getFilename();
		} elseif (empty($image)) {
			return "#";
		}
		
		if (strpos($image, $this->wwwDir) !== FALSE) {
                        $image = $this->getFromAbsolutePath($image);
                }
		
		if ($size === NULL) {
			return $this->getPath() . "/" . $this->namespace . $this->originalPrefix . "/" . $image;
		}

		list($width, $height) = explode("x", $size);
		if ($flags == NULL) {
			$flags = NImage::FIT;
		} elseif (!is_int($flags)) {
			switch (strtolower($flags)):
				case "fit":
					$flags = NImage::FIT;
					break;
				case "fill":
					$flags = NImage::FILL;
					break;
				case "exact":
					$flags = NImage::EXACT;
					break;
				case "shrink_only":
					$flags = NImage::SHRINK_ONLY;
					break;
				case "stretch":
					$flags = NImage::STRETCH;
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
				$img = NImage::fromFile($originalFile);
				if ($flags === "crop") {
					$img->crop('50%', '50%', $width, $height);
				} else {
					$img->resize($width, $height, $flags);
				}

				$this->onBeforeSaveThumbnail($img, $this->namespace, $image, $width, $height, $flags);
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
