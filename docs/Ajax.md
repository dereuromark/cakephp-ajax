# Ajax Plugin

AJAX = Asynchronous JavaScript and XML (or in this case PHP+JSON)

Basically: The X in the acronym is actually a variable. Common values of X are: XML, JSON, HTML Fragments, Plain Text.

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

You either get a 200 status code and your defined response structure:
```json
{
    "content": "[Result of our rendered template.ctp]",
    "_redirect": null
}
```

Or you get a non-200 code with the typical error structure:
```json
{
    "code": 404,
    "message": "Not Found",
    "url": "..."
}
```

A typical JS (e.g. jQuery) code can then easily use that to distinguish those two cases:
```js
$(function() {
	$('#countries').change(function() {
		var selectedValue = $(this).val();
		var targetUrl = $(this).attr('rel') + '?id=' + selectedValue;
		$.ajax({
			type: 'get',
			url: targetUrl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				if (response.content) {
					$('#provinces').html(response.content);
				}
			},
			error: function(e) {
				alert("An error occurred: " + e.responseText.message);
			}
		});
	});
});
```
