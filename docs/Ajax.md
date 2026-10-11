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

Start with [Getting started](GettingStarted.md), then read the
[component](Component/Ajax.md) and [view](View/Ajax.md) references.

## CakePHP 6 preparation

Use `fetchTable()` for model access, `viewBuilder()->setOption('serialize', ...)`
for response fields, and `$event->setResult()` or `stopPropagation()` in event
callbacks. Keep your application on current CakePHP 5 and resolve its deprecations
before upgrading. The plugin currently declares CakePHP 5 support only; its
inherited view properties and hooks need review when adding CakePHP 6 support.
