# Installation

## How to include
Installing the Plugin is pretty much as with every other CakePHP Plugin.

```
composer require dereuromark/cakephp-ajax
```

Details @ https://packagist.org/packages/dereuromark/cakephp-ajax

You then can load the plugin. In `src/Application.php`, something like:
```php
public function bootstrap() {
    parent::bootstrap();
    $this->addPlugin('Ajax');
}
```
In case you want the Ajax bootstrap file included (recommended):
```php

public function bootstrap() {
    parent::bootstrap();
    $this->addPlugin('Ajax', ['bootstrap' => true]);
}
```

Note that you do not have to load the plugin if you do not use the plugin's bootstrap or require other plugins (like IdeHelper) to know about it. It also doesn't hurt to load it, though.
