# Ajax View

A CakePHP view class to make working with AJAX a bit easier.

## Configs
First enable JSON extensions if you want to use `json` extension.
You can either use the included bootstrap file, or add the snippet manually to your own one:
```
Router::extensions(['json']);
```

## Usage
Using the `json` extension you can then access your action through the following URL:
```
/controller/action.json
```

You can enable the AjaxView class it in your actions like so:
```php
// new
$this->viewBuilder()->setClassName('Ajax.Ajax');

// old
$this->viewClass = 'Ajax.Ajax';
```
Using the AjaxComponent you can save yourself that call, as it can auto-detect AJAX request.


### Basic view rendering
Instead of GET we request it via AJAX:
```php
public function favorites() {
    $this->request->allowMethod('ajax');
    $this->viewClass = 'Ajax.Ajax'; // Only necessary without the Ajax component
}
```

The result can be this, for example:
```
{
    "content": [Result of our rendered favorites.ctp as HTML string],
    "error": ''
}
```
You can add more data to the response object via `serialize`.

### Using serialize
You can pass additional data to be included in the JSON response using the `serialize` option:

```php
$content = ['id' => 1, 'title' => 'title'];
$this->set(compact('content'));
$this->viewBuilder()->setOption('serialize', ['content']);
```

This will include the content in your JSON response:
```json
{
    "content": {"id": 1, "title": "title"},
    "error": null
}
```

You can also set serialize to `true` to include all view variables:
```php
$this->viewBuilder()->setOption('serialize', true);
```

### Special error and success variables
The AjaxView recognizes two special view variables that can simplify your responses:

#### Error responses
When you set an `error` view variable, the view will skip rendering the template and only return the error:
```php
if (!$this->process($value)) {
    $error = 'Processing failed!';
    $this->set(compact('error'));
}
```

Response:
```json
{
    "error": "Processing failed!",
    "success": null,
    "content": null
}
```

#### Success responses
Similarly, you can use the `success` variable for simple success confirmations:
```php
if ($this->process($value)) {
    $success = true; // Or a message like 'Successfully processed!'
    $this->set(compact('success'));
}
```

Response:
```json
{
    "error": null,
    "success": true,
    "content": null
}
```


### Drop down selections
```php
public function statesAjax() {
    $this->request->allowMethod('ajax');
    $id = $this->request->getQuery('id');
    if (!$id) {
        throw new NotFoundException();
    }

    $this->viewClass = 'Ajax.Ajax'; // Only necessary without the Ajax component

    $states = $this->States->getListByCountry($id);
    $this->set(compact('states'));
}
```

## Custom Plugin helpers
If your view classes needs additional plugin helpers, and you are not using the controller way anymore to load/define helpers, then you might need to extend the view class to project level and add them there:
```php
namespace App\View;

use Ajax\View\AjaxView as PluginAjaxView;

class AjaxView extends PluginAjaxView {

    /**
     * @return void
     */
    public function initialize() {
        parent::initialize();
        $this->loadHelper('...);
        ...
    }

}
```
Then make sure you load the app `Ajax` view class instead of the `Ajax.Ajax` one.
If you are using the component, you can set Configure key `'Ajax.viewClass'` to your `'Ajax'` here.

## Tips
I found the following quite useful for your jQuery AJAX code as some browsers might not properly work without it (at least for me it used to).
```
beforeSend: function(xhr) {
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
},
```
