# Installation

This development branch targets CakePHP 6 and PHP 8.5. Use it in a CakePHP 6 application.

From your application directory:

```bash
composer config minimum-stability dev
composer config prefer-stable true
composer require cakephp/cakephp:^6.0@dev dereuromark/cakephp-ajax:dev-cake6
```

Loading the plugin is optional for Composer-autoloaded classes and aliases.
Load it if you want its bootstrap to register the `.json` routing extension:

```bash
bin/cake plugin load Ajax
```

For selected route scopes, register extensions in `config/routes.php` instead:

```php
$routes->scope('/', function (\Cake\Routing\RouteBuilder $builder): void {
    $builder->setExtensions(['json']);
    $builder->fallbacks();
});
```

Then load `Ajax.Ajax` in the controllers that need automatic response handling.
See [Getting started](getting-started.md) for an example using `fetch()`.
