# Installation

## How to include
Installing the Plugin is pretty much as with every other CakePHP Plugin.

Put the files in `ROOT/plugins/Ajax`, using Packagist/Composer:
```
composer require dereuromark/cakephp-ajax:dev-master
```

or manually via

```
"require": {
	"dereuromark/cakephp-ajax": "dev-master"
}
```
and

	composer update

Details @ https://packagist.org/packages/dereuromark/cakephp-ajax

This will load the plugin (within your boostrap file):
```php
Plugin::load('Ajax');
```
or
```php
Plugin::loadAll(...);
```

In case you want the Ajax bootstrap file included (recommended), you can do that in your `ROOT/config/bootstrap.php` with

```php
Plugin::load('Ajax', ['bootstrap' => true]);
```

or

```php
Plugin::loadAll([
		'Ajax' => ['bootstrap' => true]
]);
```
