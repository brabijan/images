# Images

This is a simple image storage for [Nette Framework](http://nette.org/)

## Instalation

The best way to install brabijan/images is using  [Composer](http://getcomposer.org/):


```sh
$ composer require brabijan/images:@dev
```

After that you have to register extension in your bootstrap.php.

```php
Brabijan\Images\DI\ImagesExtension::register($configurator);
```

Package contains trait, which you have to use in class, where you want to use image storage. This works only for PHP 5.4+, for older version you can simply copy trait content and paste it into class where you want to use it.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter {

	use Brabijan\Images\TImagePipe;
	
}

```

## Usage

### Saving images

```php
/** @var Brabijan\Images\ImageStorage $imageStorage */
$imageStorage->upload($fileUpload); // saves to .../assetsDir/original/filename.jpg

$imageStorage->setNamespace("products")->upload($fileUpload); // saves to .../assetsDir/products/original/filename.jpg
```

### Using in Latte

```html
<a href="{img products/filename.jpg}"><img n:img="filename.jpg, 200x200, fill"></a>
```

output:

```html
<a href="/assetsDir/products/original/filename.jpg"><img n:img="/assetsDir/200x200_fill/filename.jpg"></a>
```

### Using in [Texy!](http://texy.info/)

```html
[img products/filename.jpg, 200x200, fill]
```

output

```html
<img src="/assetsDir/products/200x200_fill/filename.jpg">
```

### Resizing flags

For resizing (third argument) you can use these keywords - `fit`, `fill`, `exact`, `stretch`, `shrink_only`. For details see comments above [these constants](http://api.nette.org/2.0/source-common.Image.php.html#105)