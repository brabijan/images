<?php

namespace Brabijan\Images\Macros;

use Brabijan\Images\ImagePipe;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Nette;


if (!class_exists('Latte\Compiler')) {
	class_alias('Nette\Latte\Compiler', 'Latte\Compiler');
	class_alias('Nette\Latte\MacroNode', 'Latte\MacroNode');
	class_alias('Nette\Latte\PhpWriter', 'Latte\PhpWriter');
}


/**
 * @author Jan Brabec <brabijan@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
class Latte extends Nette\Latte\Macros\MacroSet
{

	/**
	 * @var bool
	 */
	private $isUsed = FALSE;



	/**
	 * @param \Nette\Latte\Compiler $compiler
	 *
	 * @return ImgMacro|\Nette\Latte\Macros\MacroSet
	 */
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);
		// todo: předání parametrů s velikostí

		/**
		 * {img [namespace/]$name[, $size[, $flags]]}
		 */
		$me->addMacro('img', array($me, 'macroImg'), NULL, array($me, 'macroAttrImg'));

		return $me;
	}



	/**
	 * @param Nette\Latte\MacroNode $node
	 * @param Nette\Latte\PhpWriter $writer
	 * @return string
	 * @throws Nette\Latte\CompileException
	 */
	public function macroImg(MacroNode $node, PhpWriter $writer)
	{
		$this->isUsed = TRUE;
		$arguments = Helpers::prepareMacroArguments($node->args);
		if ($arguments["name"] === NULL) {
			throw new Nette\Latte\CompileException("Please provide filename.");
		}

		$namespace = $arguments["namespace"];
		unset($arguments["namespace"]);
		$arguments = array_map(function ($value) use ($writer) {
			return $value ? $writer->formatWord($value) : 'NULL';
		}, $arguments);

		$command = '$_imagePipe';
		$command .= $namespace !== NULL ? '->setNamespace(' . $writer->formatWord(trim($namespace)) . ')' : '';
		$command .= '->request(' . implode(", ", $arguments) . ')';

		return $writer->write('echo %escape(' . $writer->formatWord($command) . ')');
	}



	/**
	 * @param Nette\Latte\MacroNode $node
	 * @param Nette\Latte\PhpWriter $writer
	 * @return string
	 * @throws Nette\Latte\CompileException
	 */
	public function macroAttrImg(MacroNode $node, PhpWriter $writer)
	{
		$this->isUsed = TRUE;
		$arguments = Helpers::prepareMacroArguments($node->args);
		if ($arguments["name"] === NULL) {
			throw new Nette\Latte\CompileException("Please provide filename.");
		}

		$namespace = $arguments["namespace"];
		unset($arguments["namespace"]);
		$arguments = array_map(function ($value) use ($writer) {
			return $value ? $writer->formatWord($value) : 'NULL';
		}, $arguments);

		$command = '$_imagePipe';
		$command .= $namespace !== NULL ? '->setNamespace(' . $writer->formatWord(trim($namespace)) . ')' : '';
		$command .= '->request(' . implode(", ", $arguments) . ')';

		return $writer->write('?> src="<?php echo %escape(' . $writer->formatWord($command) . ')?>" <?php');
	}



	/**
	 */
	public function initialize()
	{
		$this->isUsed = FALSE;
	}



	/**
	 * Finishes template parsing.
	 *
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		if (!$this->isUsed) {
			return array();
		}

		return array(
			get_called_class() . '::validateTemplateParams($template);',
			NULL
		);
	}



	/**
	 * @param \Nette\Templating\Template $template
	 * @throws \Nette\InvalidStateException
	 */
	public static function validateTemplateParams(Nette\Templating\Template $template)
	{
		$params = $template->getParameters();
		if (!isset($params['_imagePipe']) || !$params['_imagePipe'] instanceof ImagePipe) {
			$where = isset($params['control']) ?
				" of component " . get_class($params['control']) . '(' . $params['control']->getName() . ')'
				: NULL;

			throw new Nette\InvalidStateException(
				'Please provide an instanceof Img\\ImagePipe ' .
				'as a parameter $_imagePipe to template' . $where
			);
		}
	}

}
