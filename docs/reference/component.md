# Ajax Component

Load the component inside a controller's `initialize()` method after the parent:

```php
$this->loadComponent('Ajax.Ajax', ['actions' => ['index', 'delete']]);
```

The `actions` list limits automatic detection to those actions. An empty list
allows every action in that controller. Normal non-AJAX requests keep their view
and redirect behavior.

## Detection

By default, automatic detection checks `X-Requested-With: XMLHttpRequest`.
For a `fetch()` client that sends `Accept: application/json`, opt in:

```php
$this->loadComponent('Ajax.Ajax', [
    'detectors' => ['ajax', 'acceptJson', 'jsonExtension'],
]);
```

- `ajax` checks CakePHP's AJAX detector.
- `acceptJson` accepts `application/json` and media types ending in `+json`.
- `jsonExtension` checks the routed `_ext` parameter for `json`.

The default `ajax` detector stays active when additional detectors are configured.

The plugin does not parse JSON request bodies. Add CakePHP's
`BodyParserMiddleware` to your application middleware queue for that.

Call `$this->Ajax->enable()` or `disable()` to force response handling for the
rest of the request. The forced setting takes precedence over detection and the
`actions` list.

## Redirects

For a detected or enabled AJAX request, `beforeRedirect()` changes a controller
redirect into a rendered JSON response with HTTP status 200. The original target
and redirect status are included:

```json
{
    "error": null,
    "success": null,
    "content": null,
    "_redirect": {"url": "https://example.com/items", "status": 302}
}
```

The client decides whether to navigate to that URL. The component does not follow
it or perform an internal request. Set `resolveRedirect` to `false` to keep
normal redirects. Redirects returned directly as response objects without
`Controller::redirect()` do not trigger this callback.

## Flash messages

By default, the component consumes `Flash.<key>` from the request session, using
the loaded Flash component's `key` setting or `flash` when no component is loaded.
Consumed messages are added as `_message` in the JSON response and removed from
the session. Their shape depends on the Flash component that wrote them.

Set `flashKey` to a session key string to override it, or to `false` to preserve
messages for later HTML rendering. A custom `flashConsumer` may read or transform
messages:

```php
$this->loadComponent('Ajax.Ajax', [
    'flashConsumer' => static function (\Cake\Http\ServerRequest $request, string $key): mixed {
        return $request->getSession()->read($key);
    },
]);
```

The consumer accepts any PHP callable. It receives the request and resolved
session key. A `null` result does not add a message. `flashKey: false` disables
the consumer as well.

## Response fields

```php
$this->set('content', ['id' => 1]);
$this->viewBuilder()->setOption('serialize', ['content']);
```

The component keeps your serialized fields and adds its redirect/message keys.
See [Ajax View](view.md) for template rendering and business errors.

## Configuration

Pass options to `loadComponent()` or set application-wide defaults under the
`Ajax` key in `Configure`:

| Option | Default | Effect |
| --- | --- | --- |
| `viewClass` | `Ajax.Ajax` | View selected for an AJAX response |
| `autoDetect` | `true` | Run the configured detectors |
| `detectors` | `['ajax']` | Strategies to try |
| `resolveRedirect` | `true` | Intercept controller redirects |
| `flashKey` | `null` | Resolve the flash session key automatically |
| `flashConsumer` | `null` | Optional custom callable |
| `actions` | `[]` | Automatic-detection action list |

On CakePHP 6, custom components declare configuration defaults in `$defaultConfig`. The Ajax component's configuration keys remain unchanged.
