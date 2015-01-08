# Ajax Component
This component works together with the AjaxView to easily switch output type from HTML to JSON
format and adds some additional sugar on top.
Please see the view class docs for the main documentation.

## Usage

It will avoid redirects and pass those down as content of the JSON response object.

Don't forget `Configure::write('Ajax.flashKey', 'messages');`
if you want to use it with Tools.Flash component (stackable messages).

## Configs

- 'autoDetect' => true // Detect AJAX automatically, regardless of the extension
-	'resolveRedirect' => true // Send redirects to the view, without actually redirecting
- 'flashKey' => 'Message.flash' // Use "messages" for Tools plugin Flash component, set to false to disable

