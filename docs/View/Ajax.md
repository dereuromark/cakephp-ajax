# Ajax View

`AjaxView` extends your application's `AppView`, disables the layout, and wraps
rendered HTML in JSON. It looks for templates under an `ajax` subdirectory, for
example `templates/Items/ajax/index.php`. Put shared markup in an element if you
want both your normal template and the AJAX template to render it.

## Select the view

The component selects it when its configured detectors match. To select it
manually inside a controller action:

```php
$this->viewBuilder()->setClassName('Ajax.Ajax');
```

A `.json` extension is optional. If using it, register the extension as described
in [Installation](../Install.md); routing alone does not select this view.

## Include response fields

```php
$this->set('content', ['id' => 1, 'title' => 'Example']);
$this->set('success', true);
$this->viewBuilder()->setOption('serialize', ['content']);
```

This produces:

```json
{
    "error": null,
    "success": true,
    "content": {"id": 1, "title": "Example"}
}
```

Without `success` or `error`, the view attempts template rendering. Use `success`
for a response that needs no template. `serialize: true` includes all view
variables except the reserved configuration variables `serialize`, `_jsonOptions`,
and `_jsonp`. A list includes only its selected variables in addition to the
base response keys. Legacy `$this->set('serialize', ...)` remains supported;
the view option takes precedence when both are configured.

## Business errors

```php
$this->set('error', 'The record cannot be changed.');
```

This skips the template and sets the response's `error` value. Set an HTTP status
explicitly if your client should treat this as an HTTP failure:

```php
$this->setResponse($this->getResponse()->withStatus(422));
```

The view does not turn exceptions into JSON error responses. Use your
application's error middleware for exception handling.

## Custom view and helpers

```php
namespace App\View;

use Ajax\View\AjaxView as PluginAjaxView;

class AjaxView extends PluginAjaxView {
    public function initialize(): void {
        parent::initialize();
        $this->loadHelper('MyPlugin.MyHelper');
    }
}
```

Select your view using `$this->viewBuilder()->setClassName('Ajax')`, or configure
the component with `['viewClass' => 'Ajax']`.

JSON encoding uses `AjaxView::JSON_OPTIONS`. Override that constant in a subclass
if needed. `_jsonOptions` and `_jsonp` view variables do not configure encoding
or JSONP in this implementation.
