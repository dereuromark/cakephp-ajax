# Ajax Plugin

AJAX = Asynchronous JavaScript and XML (or in this case PHP+JSON)

## Main concept

The main concept of this plugin is to make working with AJAX in CakePHP easier and more streamlined.

That said, we first need to clarify what this plugin should not be used/needed for:
- If you only serve basic JSON responses (like API) 
- If you do not need any additional view rendering (HTML snippets), redirect prevention, flash message caption.

=> Just use JsonView instead.
 
Tip: Using RequestHandler component and `.json` extension for your URL will automatically do that for you.

### Main use cases

#### Serving HTML and JSON simultaneously 
You have an action that is rendered as HTML usually, but in one instance you need the same data via AJAX.
Instead of duplicating the action, you can leverage the Ajax Component + View class to use the same action for both output types.
Especially the rendered HTML snippet can be the same, for easier use inside the JS frontend then.
It can also on top ship the flash messages that are generated in the process.

#### Providing a consistent return object for your frontend

With JsonView your actions might sometimes return a bit inconsistent responses, missing some keys or alike. 
The AjaxView is designed to make the response more consistent.
