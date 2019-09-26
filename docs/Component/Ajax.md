# Ajax Component
This component works together with the AjaxView to easily switch output type from HTML to JSON
format and adds some additional sugar on top.
Please see the View class docs for the main documentation.

## Features
By default the CakePHP RequestHandler, when included, will prevent redirects in AJAX, **but** it will
follow those redirects and return the content via requestAction(). This might not always be desired.

This plugin prevents this internal request, and instead returns the URL and status code inside the JSON response.

### Disable internal requests
Make sure you disabled the deprecated `enableBeforeRedirect` option:
```php
$this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);
```

## Setup
Load the Ajax component inside `Controller::initialize()`:
```php
$this->loadComponent('Ajax.Ajax');
```

You can pass the settings either directly inline here, or use Configure to set them globally.

If you want to enable it only for certain actions, use the `actions` config key to whitelist certain actions.
You could also do a blacklist programmatically:
```php
/**
 * @return void
 */
public function initialize() {
    parent::initialize();
    ...

    if (!in_array($this->request->getParam('action'), ['customAction'], true)) {
        $this->loadComponent('Ajax.Ajax');
    }
}
```
But in general, a whitelist setup is usually recommended.

## Usage
This component will avoid those redirects completely and pass those down as part of the content of the JSON response object:

    "_redirect":{"url":"http://controller/action","status":200}, ...

Flash messages are also caught and passed down as part of the response:

    "_message":{"success":["Yeah, that was a normal POST and redirect (PRG)."]}, ...

Don't forget `Configure::write('Ajax.flashKey', 'FlashMessage');`
if you want to use it with Tools.Flash component (multi/stackable messages).

You can pass content along with it, as well, those JSON response keys will not be prefixed with a `_` underscore then, as they
are not reserved:
```php
$content = ['id' => 1, 'title' => 'title'];
$this->set(compact('content'));
$this->set('_serialize', ['content']);
```
results in

    "content":{...}, ...
    
### AJAX Delete

For usability reasons you might want to delete a row in a paginated table, without the need to refresh the whole page.
All you need to do here is
- Add a specific class to the "post link"
- Add some custom JS to catch the "post link JS"
- Make sure the AjaxComponent is loaded for this action

The default bake action usually already works perfectly:

```php
public function delete($id = null) {
    $this->request->allowMethod(['post', 'delete']);
    $group = $this->Groups->get($id);

    $this->Groups->delete($group);
    $this->Flash->success(__('The group has been deleted.'));

    return $this->redirect(['action' => 'index']);
}
```
The JSON response even contains the flash message and redirect URL in case you want to use that in your JS response handling:
```
{
    "error":null,
    "content":null,
    "_message":[{"message":"The group has been deleted.","key":"flash","element":"Flash\/success","params":[]}],
    "_redirect":{"url":"http:\/\/app.local\/groups","status":302}
}
```

If you have some custom "fail" logic, though, you need to do a small adjustment.
Then just modify your delete action to pass down the error to the view for cases where this is needed:
```php
public function delete($id = null) {
    $this->request->allowMethod(['post', 'delete']);
    $group = $this->Groups->get($id);

    if ($group->status === $group::STATUS_PUBLIC) {
        $error = 'Already public, deleting not possible in that state.';
        $this->Flash->error($error);
        $this->set(compact('error'));

        // Since we are not deleting, referer redirect is safe to use here
        return $this->redirect($this->referer(['action' => 'index'], true));
    }

    $this->Groups->delete($group);
    $this->Flash->success(__('The group has been deleted.'));

    return $this->redirect(['action' => 'index']);
}
```

If you don't pass the error to the view, you would need to read/parse the passed flash messages (key=error), which could be a bit more difficult to do.
But the adjustment above is still minimal (1-2 lines difference from the baked default action for delete case).

The nice bonus is the auto-fallback: The controller and all deleting works normally for those that have JS disabled.

A live example can be found in the [Sandbox](https://sandbox.dereuromark.de/sandbox/ajax-examples/table).

### Simple boolean response

In cases like "edit in place" you often just need a basic AJAX response as boolean YES/NO, maybe with an error message on top.
Since we ideally always return a 200 OK response, we need a different way of signaling the frontend if the operation was successful.

Here you can simplify it using the special "error"/"success" keys that auto-format the reponse as JSON:
```php
$this->request->allowMethod('post');

$value = $this->request->getData('value');
if (!$this->process($value)) {
    $error = 'Didnt work out!';
    $this->set(compact('error'));
} else {
    $success = true; // Or a text like 'You did it!'
    $this->set(compact('success'));
}
```

In the case of x-editable as "edit in place" JS all you need is to check for the error message:
```js
success: function(response, newValue) {
    if (response.error) {
        return response.error;  //msg will be shown in editable form
    }
}
```

## Configs

- 'autoDetect' => true // Detect AJAX automatically, regardless of the extension
- 'resolveRedirect' => true // Send redirects to the view, without actually redirecting
- 'flashKey' => 'Message.flash' // Set to false to disable
- 'actions' => [] // Set to an array of actions if you want to only whitelist these specific actions
