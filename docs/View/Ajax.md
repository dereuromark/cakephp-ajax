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
$this->viewBuilder->setClassName('Ajax.Ajax');

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
You can add more data to the response object via `_serialize`.


### Drop down selections
```php
public function statesAjax() {
    $this->request->allowMethod('ajax');
    $id = $this->request->query('id');
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
