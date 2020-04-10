# CakePHP Ajax Plugin
[![Build Status](https://api.travis-ci.com/dereuromark/cakephp-ajax.svg?branch=cake2)](https://travis-ci.org/dereuromark/cakephp-ajax)
[![Coverage Status](https://coveralls.io/repos/dereuromark/cakephp-ajax/badge.png?branch=2.x)](https://coveralls.io/r/dereuromark/cakephp-ajax)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-ajax/v/stable.png)](https://packagist.org/packages/dereuromark/cakephp-ajax)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%205.4-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-ajax/license.png)](https://packagist.org/packages/dereuromark/cakephp-ajax)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-ajax/d/total.png)](https://packagist.org/packages/dereuromark/cakephp-ajax)

A CakePHP 2.x plugin that makes working with AJAX a piece of cake.

NOTE: With 4.x development already being started, **this 2.x branch is now in maintenance mode**. No active development is done anymore on it, mainly only necessary bugfixes.

## What is this plugin for?
Basically DRY (Don't repeat yourself) and easy AJAX handling.
See  [my article](http://www.dereuromark.de/2014/01/09/ajax-and-cakephp/) for details on the history of this view class.

### Key features
- Auto-handling via View class mapping and making controller actions available both AJAX and non-AJAX by design.
- Flash message and redirect (prevention) support.

## How to include
Installing the plugin is pretty much as with every other CakePHP Plugin.

* Put the files in `APP/Plugin/Ajax`.
* Make sure you have `CakePlugin::load('Ajax')` or `CakePlugin::loadAll()` in your bootstrap.

You should use composer/packagist now @ https://packagist.org/packages/dereuromark/cakephp-ajax

```
"require": {
	"dereuromark/cakephp-ajax": "2.x-dev"
}
```

That's it. It should be up and running.

## Usage
- [Documentation](Docs/README.md)
