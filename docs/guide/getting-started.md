# Getting started

Follow [Installation](installation.md) first. This example adds a JSON response to an
existing CakePHP controller. It needs no database changes, route extensions, or
JavaScript library.

## Configure one controller

In `src/Controller/ExamplesController.php`:

```php
namespace App\Controller;

class ExamplesController extends AppController {
    public function initialize(): void {
        parent::initialize();
        $this->loadComponent('Ajax.Ajax', [
            'actions' => ['ping'],
            'detectors' => ['acceptJson'],
        ]);
    }

    public function ping(): void {
        $this->request->allowMethod('get');
        $this->set([
            'success' => true,
            'content' => ['message' => 'Connected'],
        ]);
        $this->viewBuilder()->setOption('serialize', ['content']);
    }
}
```

`success` makes AjaxView skip template rendering. The `serialize` option includes
the `content` variable in the response. This action therefore needs no template
for the JSON request.

Use your application's existing routing for `/examples/ping`. Apply its normal
authentication and authorization rules if the endpoint requires access control.
A request without a JSON Accept header or the XMLHttpRequest header uses the
normal application view;
provide a template if you also want to support that request.

## Call it from a template

Use CakePHP's URL helper so applications installed under a subdirectory work:

```php
<button id="ping" type="button">Check connection</button>
<pre id="ping-result"></pre>
<script>
document.querySelector('#ping').addEventListener('click', async () => {
    const url = <?= json_encode($this->Url->build(['controller' => 'Examples', 'action' => 'ping']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const output = document.querySelector('#ping-result');
    try {
        const response = await fetch(url, {headers: {Accept: 'application/json'}});
        if (!response.ok) {
            throw new Error(`Request failed (${response.status})`);
        }
        const data = await response.json();
        output.textContent = data.error || data.content.message;
    } catch (error) {
        output.textContent = error.message;
    }
});
</script>
```

The JSON contains `error: null`, `success: true`, and the content object.
For form submissions, keep CakePHP's CSRF and FormProtection handling in place;
the plugin does not replace them.

See [Ajax View](../reference/view.md) for HTML fragments and
[Ajax Component](../reference/component.md) for redirects and flash messages.
