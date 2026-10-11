# Installation

From your application directory:

```bash
composer require dereuromark/cakephp-ajax
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
See [Getting started](GettingStarted.md) for an example using `fetch()`.
