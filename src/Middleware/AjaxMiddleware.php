<?php

namespace Ajax\Middleware;

use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Ajax Middleware to respond to AJAX requests.
 *
 * Works together with the AjaxView to easily switch
 * output type from HTML to JSON format. Replaces some
 * of the component functionality when the Authorization
 * middleware is in use.
 *
 * It will also avoid redirects and pass those down as content
 * of the JSON response object.
 *
 * @author Greg Schmidt, with much code copied from the AjaxComponent class by Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class AjaxMiddleware {

	use InstanceConfigTrait;

	/**
	 * @var \Cake\Controller\Controller
	 */
	protected $Controller;

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'viewClass' => 'Ajax.Ajax',
		'autoDetect' => true,
		'resolveRedirect' => true,
		'flashKey' => 'Flash.flash',
		'actions' => [],
		'jsonOptions' => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR,
	];

	/**
	 * Constructor
	 *
	 * @param array|string $config Array of configuration settings or string with authentication service provider name.
	 */
	public function __construct($config = []) {
		$defaults = (array)Configure::read('Ajax') + $this->_defaultConfig;
		$config += $defaults;
		$this->setConfig($config);
	}

	/**
	 * Callable implementation for the middleware stack.
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request The request.
	 * @param \Psr\Http\Message\ResponseInterface $response The response.
	 * @param callable $next The next middleware to call.
	 * @return \Psr\Http\Message\ResponseInterface A response.
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next) {
		$respondAsAjax = $this->_config['autoDetect'] && $this->_isActionEnabled($request) && $request->is('ajax');
		if ($respondAsAjax) {
			EventManager::instance()->on('Controller.beforeRender', [$this, 'beforeRender']);
		}

		$response = $next($request, $response);

		if ($respondAsAjax) {
			EventManager::instance()->off('Controller.beforeRender', [$this, 'beforeRender']);
			if ($this->_config['resolveRedirect'] && $response->hasHeader('Location')) {
				$response = $this->_redirect($request, $response);
			}
		}

		return $response;
	}

	protected function _redirect(ServerRequestInterface $request, ResponseInterface $response) {
		$message = $request->getSession()->consume($this->_config['flashKey']);
		$url = $response->getHeader('Location')[0];
		$status = $response->getStatusCode();

		$response = $response->withStatus(200)
			->withoutHeader('Location')
			->withHeader('Content-Type', 'application/json; charset=' . $response->charset())
			->withStringBody(json_encode([
				// Error and content are here to make the output the same as previously
				// with the component, so existing unit tests don't break.
				'error' => null,
				'content' => null,
				'_message' => $message,
				'_redirect' => compact('url', 'status'),
			], $this->_config['jsonOptions']));

		return $response;
	}

	/**
	 * Checks to see if the Controller->viewVar labeled _serialize is set to boolean true.
	 *
	 * @return bool
	 */
	protected function _isControllerSerializeTrue() {
		if (!empty($this->Controller->viewVars['_serialize']) && $this->Controller->viewVars['_serialize'] === true) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if we are using action whitelisting and if so checks if this action is whitelisted.
	 *
	 * @return bool
	 */
	protected function _isActionEnabled(ServerRequestInterface $request) {
		$actions = $this->getConfig('actions');
		if (!$actions) {
			return true;
		}

		return in_array($request->getParam('action'), $actions, true);
	}

	/**
	 * Called before the Controller::beforeRender(), and before
	 * the view class is loaded, and before Controller::render()
	 *
	 * @param \Cake\Event\Event $event
	 * @return void
	 */
	public function beforeRender(Event $event) {
		$this->Controller = $event->getSubject();
		$this->Controller->viewBuilder()->setClassName($this->_config['viewClass']);

		// Set flash messages to the view
		if ($this->_config['flashKey']) {
			$message = $this->Controller->request->getSession()->consume($this->_config['flashKey']);
			if ($message || !array_key_exists('_message', $this->Controller->viewVars)) {
				$this->Controller->set('_message', $message);
			}
		}

		// If _serialize is true, *all* viewVars will be serialized; no need to add _message.
		if ($this->_isControllerSerializeTrue()) {
			return;
		}

		$serializeKeys = ['_message'];
		if (!empty($this->Controller->viewVars['_serialize'])) {
			$serializeKeys = array_merge((array)$this->Controller->viewVars['_serialize'], $serializeKeys);
		}
		$this->Controller->set('_serialize', $serializeKeys);
	}

}
