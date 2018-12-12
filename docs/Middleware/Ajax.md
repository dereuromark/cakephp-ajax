# Ajax Middleware
This middleware provides an alternative to the component, and is intended for applications which have migrated to the
middleware stack provided in CakePHP 3.5 and above.
Features and configuration are intended to be the same between the two methods, you should only need one or
the other.
Refer to [Component documentation](../Component/Ajax.md) for those details.

## Usage
Load the Ajax middleware:
```php
$middlewareQueue->add(\Ajax\Middleware\AjaxMiddleware::class);
```

As with the component, you can pass the settings either directly inline here, or use Configure to set them globally.

```php
$middlewareQueue->add(new AjaxMiddleware(['viewClass' => 'MyAjax']))
```

If you're converting from the component, remember to it from your controller initialization.

That should be it! All your existing functionality and unit tests should just work.