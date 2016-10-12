<?php


namespace Brabijan\Images\Tests;


use Brabijan\Images\Size;

class SizeTest extends \PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		parent::__construct();
		$containerFactory = new ContainerFactory;
		$this->container = $containerFactory->create();
	}

	public function testDimensionsGetters()
	{
		$testSubject = new Size(123, 987);
		static::assertSame(123, $testSubject->getWidth());
		static::assertSame(987, $testSubject->getHeight());

		$testSubject = new Size(123.123, 987.987);
		static::assertSame(123.123, $testSubject->getWidth());
		static::assertSame(987.987, $testSubject->getHeight());
	}

	public function testFromFile()
	{
		$subject = Size::fromFile(__DIR__.'/assets/screen-city.png');
		static::assertSame(1108, $subject->getWidth());
		static::assertSame(608, $subject->getHeight());

	}
}
