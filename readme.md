# Images

This is a simple image storage for [Nette Framework](http://nette.org/)

## Instalation

The best way to install brabijan/images is using  [Composer](http://getcomposer.org/):


```sh
$ composer require brabijan/images:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	- Brabijan\Images\DI\ImagesExtension
```

Package contains trait, which you will have to use in class, where you want to use image storage. This works only for PHP 5.4+, for older version you can simply copy trait content and paste it into class where you want to use it.

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

### Using in Latte

```html
<a href="{img products/filename.jpg}"><img n:img="filename.jpg, 200x200, fill"></a>
```

output:

```html
<a href="/assetsDir/products/original/filename.jpg"><img src="/assetsDir/200x200_4/filename.jpg"></a>
```

### Using in [Texy!](http://texy.info/)

First you have to register macro into Texy!

```php
$texy = new Texy;
$this->registerTexyMacros($texy);
```

Now you can just use it. Macro expands native image macro in Texy. Here is the syntax.

```html
[* products/filename.jpg, 200x200, fill *]
```

If file not found in image storage, macro try to search file in document root. Of course you can add title or floating of image, as you know from pure Texy!

### Resizing flags

For resizing (third argument) you can use these keywords - `fit`, `fill`, `exact`, `stretch`, `shrink_only`. For details see comments above [these constants](http://api.nette.org/2.0/source-common.Image.php.html#105)
