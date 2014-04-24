<?php

namespace Brabijan\Images\Forms;
use Kdyby;
use Nette;
use Nette\Http;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @method onUpload(UploadControl $control, array $files)
 */
class UploadControl extends Nette\Forms\Controls\BaseControl
{

	/**
	 * @var array of function (UploadControl $control, Http\FileUpload[] $files)
	 */
	public $onUpload = array();

	/**
	 * @var Http\Request
	 */
	private $httpRequest;

	/**
	 * @var Http\Response
	 */
	private $httpResponse;



	/**
	 * @param null|string $label
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->monitor('Nette\Application\UI\Presenter');
		$this->control->type = 'file';
	}



	/**
	 * @param \Nette\ComponentModel\Container $parent
	 * @throws \Nette\InvalidStateException
	 * @return void
	 */
	protected function attached($parent)
	{
		if ($parent instanceof Nette\Forms\Form) {
			if ($parent->getMethod() !== Nette\Forms\Form::POST) {
				throw new Nette\InvalidStateException('File upload requires method POST.');
			}
			$parent->getElementPrototype()->enctype = 'multipart/form-data';

		} elseif ($parent instanceof Nette\Application\UI\Presenter) {
			if (!$this->httpRequest) {
				$this->httpRequest = $parent->getContext()->httpRequest;
				$this->httpResponse = $parent->getContext()->httpResponse;
			}
		}

		parent::attached($parent);
	}



	/**
	 * @return UploadControl
	 */
	public function allowMultiple()
	{
		$this->control->multiple = TRUE;

		return $this;
	}



	/**
	 * Sets control's value.
	 *
	 * @param  array|Nette\Http\FileUpload
	 * @return Nette\Http\FileUpload  provides a fluent interface
	 */
	public function setValue($value)
	{
		if (is_array($value)) {
			if (Nette\Utils\Validators::isList($value)) {
				foreach ($value as $i => $file) {
					$this->value[$i] = $file instanceof Http\FileUpload ? $file : new Http\FileUpload($file);
				}

			} else {
				$this->value = array(new Http\FileUpload($value));
			}

		} elseif ($value instanceof Http\FileUpload) {
			$this->value = array($value);

		} else {
			$this->value = new Http\FileUpload(NULL);
		}

		return $this;
	}



	public function loadHttpData()
	{
		parent::loadHttpData();

		if ($this->value) {
			$this->onUpload($this, $this->value);
		}
	}



	/**
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . '[]';
	}



	/**
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()->data('url', $this->form->action);
	}



	/**
	 * Has been any file uploaded?
	 *
	 * @return bool
	 */
	public function isFilled()
	{
		foreach ((array) $this->value as $file) {
			if (!$file instanceof Http\FileUpload || !$file->isOK()) {
				return FALSE;
			}
		}

		return (bool) $this->value;
	}



	/**
	 * Image validator: is file image?
	 *
	 * @param UploadControl $control
	 * @return bool
	 */
	public static function validateImage(UploadControl $control)
	{
		foreach ((array) $control->value as $file) {
			if (!$file instanceof Http\FileUpload || !$file->isImage()) {
				return FALSE;
			}
		}

		return (bool) $control->value;
	}

}
