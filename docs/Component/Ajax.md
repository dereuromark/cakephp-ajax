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

## Usage
Load the Ajax component:
```php
public $components = ['Ajax.Ajax'];
```

You can pass the settings either directly inline here, or use Configure to set them globally.

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
$this->Controller->set(compact('content'));
$this->Controller->set('_serialize', ['content']);
```
results in

    "content":{...}, ...
    
### AJAX Delete

For usability reasons you might want to delete a row in a paginated table, without the need to refresh the whole page.
All you need to do here is
- Add a specific class to the "post link"
- Add some custom JS to catch the "post link JS"
- Make sure the AjaxComponent is loaded for this action

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

The nice bonus is the auto-fallback: The controller and all deleting works normally for those that have JS disabled.

A live example can be found in the [Sanbox](https://sandbox.dereuromark.de/sandbox/ajax-examples/table).

## Configs

- 'autoDetect' => true // Detect AJAX automatically, regardless of the extension
- 'resolveRedirect' => true // Send redirects to the view, without actually redirecting
- 'flashKey' => 'Message.flash' // Set to false to disable
- 'actions' => [] // Set to an array of actions if you want to only whitelist these specific actions
