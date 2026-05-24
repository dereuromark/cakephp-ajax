<?php
declare(strict_types=1);

namespace Ajax\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Routing\Router;
use Closure;

/**
 * Ajax Component to respond to AJAX requests.
 *
 * Works together with the AjaxView to easily switch
 * output type from HTML to JSON format.
 *
 * It will also avoid redirects and pass those down as content
 * of the JSON response object.
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class AjaxComponent extends Component {

	/**
	 * @var bool
	 */
	public bool $respondAsAjax = false;

	/**
	 * Force-state set via enable()/disable(). When non-null, takes precedence over autoDetect.
	 *
	 * @var bool|null
	 */
	protected ?bool $forced = null;

	/**
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'viewClass' => 'Ajax.Ajax',
		'autoDetect' => true,
		'detectors' => ['ajax'],
		'resolveRedirect' => true,
		'flashKey' => null,
		'flashConsumer' => null,
		'actions' => [],
	];

	/**
	 * @param \Cake\Controller\ComponentRegistry $collection
	 * @param array<string, mixed> $config
	 */
	public function __construct(ComponentRegistry $collection, $config = []) {
		$defaults = (array)Configure::read('Ajax') + $this->_defaultConfig;
		$config += $defaults;
		parent::__construct($collection, $config);
	}

	/**
	 * @param array<string, mixed> $config
	 * @return void
	 */
	public function initialize(array $config): void {
		if ($this->forced !== null) {
			$this->respondAsAjax = $this->forced;

			return;
		}
		if (!$this->_config['autoDetect'] || !$this->_isActionEnabled()) {
			return;
		}
		$this->respondAsAjax = $this->_shouldRespondAsAjax();
	}

	/**
	 * Force AJAX response handling on for the rest of the request, regardless of autoDetect / X-Requested-With.
	 *
	 * Useful from `beforeFilter()` or controller actions when responding to non-XHR clients (fetch without
	 * X-Requested-With, integration tests, internal sub-requests).
	 *
	 * @return void
	 */
	public function enable(): void {
		$this->forced = true;
		$this->respondAsAjax = true;
	}

	/**
	 * Force AJAX response handling off, regardless of autoDetect.
	 *
	 * @return void
	 */
	public function disable(): void {
		$this->forced = false;
		$this->respondAsAjax = false;
	}

	/**
	 * Called before the Controller::beforeRender(), and before
	 * the view class is loaded, and before Controller::render()
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @return void
	 */
	public function beforeRender(EventInterface $event): void {
		if (!$this->respondAsAjax) {
			return;
		}

		$this->_respondAsAjax();
	}

	/**
	 * @return void
	 */
	protected function _respondAsAjax(): void {
		$this->getController()->viewBuilder()->setClassName($this->_config['viewClass']);

		// Set flash messages to the view. Skipped when flashKey is explicitly false.
		if ($this->_config['flashKey'] !== false) {
			$message = $this->_consumeFlash();
			if ($message !== null) {
				$this->getController()->set('_message', $message);
			}
		}

		// If `serialize` is true, *all* viewVars will be serialized; no need to add _message.
		if ($this->_isControllerSerializeTrue()) {
			return;
		}

		$serializeKeys = ['_message'];
		if ($this->getController()->viewBuilder()->getVar('serialize')) {
			$serializeKeys = array_merge($serializeKeys, (array)$this->getController()->viewBuilder()->getVar('serialize'));
		}
		$this->getController()->set('serialize', $serializeKeys);
	}

	/**
	 * @return bool
	 */
	protected function _shouldRespondAsAjax(): bool {
		$request = $this->getController()->getRequest();

		foreach ((array)$this->getConfig('detectors') as $detector) {
			if ($detector === 'ajax' && $request->is('ajax')) {
				return true;
			}
			if ($detector === 'acceptJson' && $this->_acceptsJson($request->getHeaderLine('Accept'))) {
				return true;
			}
			if ($detector === 'jsonExtension' && $request->getParam('_ext') === 'json') {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $acceptHeader
	 * @return bool
	 */
	protected function _acceptsJson(string $acceptHeader): bool {
		if ($acceptHeader === '') {
			return false;
		}

		foreach (explode(',', $acceptHeader) as $part) {
			$mediaType = trim(strtolower(explode(';', $part)[0]));
			if ($mediaType === 'application/json' || str_ends_with($mediaType, '+json')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Called before Controller::redirect(). Allows you to replace the URL that will
	 * be redirected to with a new URL.
	 *
	 * @param \Cake\Event\EventInterface $event Event
	 * @param array<mixed>|string $url Either the string or URL array that is being redirected to.
	 * @param \Cake\Http\Response $response
	 * @return void
	 */
	public function beforeRedirect(EventInterface $event, $url, Response $response): void {
		if (!$this->respondAsAjax || !$this->_config['resolveRedirect']) {
			return;
		}

		$url = Router::url($url, true);

		$status = $response->getStatusCode();
		$response = $response->withStatus(200)->withoutHeader('Location');
		$this->getController()->setResponse($response);

		$this->getController()->enableAutoRender();
		$this->getController()->set('_redirect', ['url' => $url, 'status' => $status]);

		$event->stopPropagation();

		if ($this->_isControllerSerializeTrue()) {
			return;
		}

		$serializeKeys = ['_redirect'];
		if ($this->getController()->viewBuilder()->getVar('serialize')) {
			$serializeKeys = array_merge($serializeKeys, (array)$this->getController()->viewBuilder()->getVar('serialize'));
		}
		$this->getController()->set('serialize', $serializeKeys);
		// Further changes will be required here when the change to immutable response objects is completed
		$response = $this->getController()->render();
		$event->setResult($response);
	}

	/**
	 * Resolves the configured flash session key and consumes its contents.
	 *
	 * Resolution order:
	 *  - If `flashConsumer` is a callable, delegate entirely (it returns whatever shape it likes).
	 *  - If `flashKey` is a non-empty string, use it verbatim (BC with the previous `'Flash.flash'` default).
	 *  - Otherwise resolve from the loaded FlashComponent's `key` config (defaults to `flash`),
	 *    yielding `Flash.<key>` — so an app that reconfigured FlashComponent picks up automatically.
	 *
	 * @return mixed Returns null when no flash data is present.
	 */
	protected function _consumeFlash(): mixed {
		$consumer = $this->_config['flashConsumer'];
		if ($consumer instanceof Closure || (is_object($consumer) && method_exists($consumer, '__invoke')) || (is_string($consumer) && function_exists($consumer))) {
			return $consumer($this->getController()->getRequest(), $this->_resolveFlashKey());
		}

		$key = $this->_resolveFlashKey();

		return $this->getController()->getRequest()->getSession()->consume($key);
	}

	/**
	 * @return string Session key to consume, e.g. `Flash.flash`.
	 */
	protected function _resolveFlashKey(): string {
		$configured = $this->_config['flashKey'];
		if (is_string($configured) && $configured !== '') {
			return $configured;
		}

		$controller = $this->getController();
		if ($controller->components()->has('Flash')) {
			$flash = $controller->components()->get('Flash');
			$flashKey = (string)$flash->getConfig('key', 'flash');

			return 'Flash.' . $flashKey;
		}

		return 'Flash.flash';
	}

	/**
	 * Checks to see if the Controller->viewVar labeled `serialize` is set to boolean true.
	 *
	 * @return bool
	 */
	protected function _isControllerSerializeTrue(): bool
    {
        return $this->getController()->viewBuilder()->getVar('serialize') === true;
    }

	/**
	 * Checks if we are using action whitelisting and if so checks if this action is whitelisted.
	 *
	 * @return bool
	 */
	protected function _isActionEnabled(): bool {
		$actions = $this->getConfig('actions');
		if (!$actions) {
			return true;
		}

		return in_array($this->getController()->getRequest()->getParam('action'), $actions, true);
	}

}
