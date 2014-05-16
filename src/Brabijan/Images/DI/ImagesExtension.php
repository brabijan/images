<?php

namespace Brabijan\Images\DI;

use Latte;
use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;


if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Configurator', 'Nette\DI\Configurator');
}

if (!class_exists('Latte\Engine')) {
	class_alias('Nette\Latte\Engine', 'Latte\Engine');
	class_alias('Nette\Templating\Template', 'Latte\Template');
}

/**
 * @author Jan Brabec <brabijan@gmail.com>
 */
class ImagesExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();
		$engine = $builder->getDefinition('nette.latte');

		$install = 'Brabijan\Images\Macros\Latte::install';

		if (method_exists('Latte\Engine', 'getCompiler')) {
			$engine->addSetup($install . '(?->getCompiler())', array('@self'));
		} else {
			$engine->addSetup($install . '(?->compiler)', array('@self'));
		}

		$builder->addDefinition($this->prefix('imagePipe'))->setClass('Brabijan\Images\ImagePipe', array($config['assetsDir'],
			$this->containerBuilder->parameters['wwwDir']))
			->addSetup('setAssetsDir', array($config['assetsDir']));
		$builder->addDefinition($this->prefix('imageStorage'))->setClass('Brabijan\Images\ImageStorage', array($config['assetsDir']));
		$builder->addDefinition($this->prefix('fileBrowser'))->setClass('Brabijan\Images\FileBrowser');
	}



	/**
	 * @param \Nette\Config\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Configurator $config, $extensionName = 'imagesExtension')
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new ImagesExtension());
		};
	}



	/**
	 * {@inheritdoc}
	 */
	public function getConfig(array $defaults = NULL, $expand = TRUE)
	{
		$defaults = array(
			'storageDir' => $this->containerBuilder->parameters['wwwDir'] . '/assets',
			'assetsDir' => $this->containerBuilder->parameters['wwwDir'] . '/assets'
		);

		return parent::getConfig($defaults, $expand);
	}

}
