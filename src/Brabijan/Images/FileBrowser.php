<?php

namespace Brabijan\Images;

use Nette;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

class FileBrowser extends Nette\Object
{

	/** @var string */
	private $assetsDir;

	/** @var array */
	private $generatedDirs;

	/** @var string */
	private $originalPrefix;



	/**
	 * @param ImagePipe $imagePipe
	 */
	public function __construct(ImagePipe $imagePipe)
	{
		$this->assetsDir = $imagePipe->getAssetsDir();
		$this->originalPrefix = $imagePipe->getOriginalPrefix();
		$this->generatedDirs = array(
			$imagePipe->getOriginalPrefix(),
			'[0-9]_[0-9]*x[0-9]*'
		);
	}



	/**
	 * Returns all files in namespace
	 *
	 * @param null $namespace
	 * @return \SplFileInfo[]
	 */
	public function getNamespaceFiles($namespace = NULL)
	{
		$files = array();
		$imageDir = $this->assetsDir . ($namespace ? DIRECTORY_SEPARATOR . $namespace : "") . DIRECTORY_SEPARATOR . $this->originalPrefix;
		/** @var $file \SplFileInfo */
		foreach (Finder::findFiles("*")->in($this->assetsDir, $imageDir) as $file) {
			$files[] = $file;
		}

		return $files;
	}



	/**
	 * Returns all declared namespaces
	 *
	 * @return \SplFileInfo[]
	 */
	public function getDeclaredNamespaces()
	{
		$namespaces = array();
		/** @var $file \SplFileInfo */
		foreach (Finder::findDirectories("*")->in($this->assetsDir)->exclude($this->generatedDirs) as $file) {
			$namespaces[] = $file->getFilename();
		}

		return $namespaces;
	}



	/**
	 * @param $param
	 * @throws FileNotFoundException
	 * @return string
	 */
	public function find($param)
	{
		foreach (Finder::findFiles($param)->from($this->assetsDir) as $file) {
			/** @var \SplFileInfo $file */
			return $file->getPathname();
		}

		throw new FileNotFoundException("File $param not found.");
	}

}