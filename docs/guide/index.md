# Ajax Plugin

Use this plugin when a controller action should return an HTML fragment inside a
JSON response, or when a JavaScript client needs redirect URLs and flash messages.
For a plain JSON API, CakePHP's core `JsonView` is usually enough. Register core
view classes through `Controller::viewClasses()` for content negotiation.

## Response shape

AjaxView starts with these keys:

```json
{
    "error": null,
    "success": null,
    "content": null
}
```

`content` contains rendered template HTML unless you replace it with a serialized
view variable. Setting a non-null `error` or `success` skips template rendering.
Additional serialized variables are included as extra keys. `_message` appears
when the component consumes flash messages; `_redirect` appears when it intercepts
a controller redirect.

The plugin preserves normal HTTP errors. Exception response formatting depends
on your application's error handling and content negotiation. Check `response.ok`
in a `fetch()` client before handling a successful response body. A business error
set through the `error` variable does not change the HTTP status by itself.

Start with [Getting started](getting-started.md), then read the
[component](../reference/component.md) and [view](../reference/view.md) references.

## CakePHP 6 branch

This branch runs on CakePHP 6 and PHP 8.5. Use `fetchTable()` for model access,
`viewBuilder()->setOption('serialize', ...)` for response fields, and
`$event->setResult()` or `stopPropagation()` in event callbacks. Follow the
[installation instructions](installation.md) for the development dependencies.
