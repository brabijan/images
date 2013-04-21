<?php

namespace Brabijan\Images;

use Nette;

/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @author Jan Brabec <brabijan@gmail.com>
 */
trait TImagePipe
{

	/** @var ImagePipe */
	public $imgPipe;



	/**
	 * @param ImagePipe $imgPipe
	 */
	public function injectImgPipe(ImagePipe $imgPipe)
	{
		$this->imgPipe = $imgPipe;
	}



	/**
	 * @param string $class
	 *
	 * @return Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		/** @var \Nette\Templating\FileTemplate|\stdClass $template */
		$template->_imagePipe = $this->imgPipe;

		return $template;
	}

}