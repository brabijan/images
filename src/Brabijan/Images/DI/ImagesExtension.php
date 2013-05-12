<?php

namespace Brabijan\Images\DI;

use Nette,
	Nette\Config\Compiler,
	Nette\Config\Configurator;

/**
 * @author Jan Brabec <brabijan@gmail.com>
 */
class ImagesExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();
		$engine = $builder->getDefinition('nette.latte');

		$install = 'Brabijan\Images\Macros\Latte::install';
		$engine->addSetup($install . '(?->compiler)', array('@self'));

		$builder->addDefinition($this->prefix('imagePipe'))->setClass('Brabijan\Images\ImagePipe', array($config["assetsDir"],
			$this->containerBuilder->parameters["wwwDir"]))
			->addSetup("setAssetsDir", array($config["assetsDir"]));
		$builder->addDefinition($this->prefix('imageStorage'))->setClass('Brabijan\Images\ImageStorage', array($config["assetsDir"]));
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
			"storageDir" => $this->containerBuilder->parameters["wwwDir"] . "/assets",
			"assetsDir" => $this->containerBuilder->parameters["wwwDir"] . "/assets"
		);

		return parent::getConfig($defaults, $expand);
	}

}