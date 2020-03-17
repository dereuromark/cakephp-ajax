<?php

namespace Ajax\Middleware;

use Ajax\View\AjaxView;
use Ajax\View\JsonEncoder;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
class AjaxMiddleware implements MiddlewareInterface {

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
		'jsonOptions' => AjaxView::JSON_OPTIONS,
	];

	/**
	 * @param array $config Array of configuration settings
	 */
	public function __construct(array $config = []) {
		$defaults = (array)Configure::read('Ajax') + $this->_defaultConfig;
		$config += $defaults;
		$this->setConfig($config);
	}

	/**
	 * @param \Cake\Http\ServerRequest $request
	 * @param \Psr\Http\Server\RequestHandlerInterface $handler
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$respondAsAjax = $this->_config['autoDetect'] && $this->_isActionEnabled($request) && $request->is('ajax');
		if ($respondAsAjax) {
			EventManager::instance()->on('Controller.beforeRender', [$this, 'beforeRender']);
		}

		/** @var \Cake\Http\Response $response */
		$response = $handler->handle($request);

		if ($respondAsAjax) {
			EventManager::instance()->off('Controller.beforeRender', [$this, 'beforeRender']);
			if ($this->_config['resolveRedirect'] && $response->hasHeader('Location')) {
				$response = $this->_redirect($request, $response);
			}
		}

		return $response;
	}

	/**
	 * Generate a JSON response encoding the redirect
	 *
	 * @param \Cake\Http\ServerRequest $request The request.
	 * @param \Cake\Http\Response $response The response.
	 * @return \Cake\Http\Response A response.
	 * @throws \RuntimeException
	 */
	protected function _redirect(ServerRequest $request, Response $response) {
		$message = $request->getSession()->consume($this->_config['flashKey']);
		$url = $response->getHeader('Location')[0];
		$status = $response->getStatusCode();

		$json = JsonEncoder::encode([
			// Error and content are here to make the output the same as previously
			// with the component, so existing unit tests don't break.
			'error' => null,
			'content' => null,
			'_message' => $message,
			'_redirect' => compact('url', 'status'),
		], $this->_config['jsonOptions']);

		$response = $response->withStatus(200)
			->withoutHeader('Location')
			->withHeader('Content-Type', 'application/json')
			->withStringBody($json);

		return $response;
	}

	/**
	 * Checks to see if the Controller->viewVar labeled _serialize is set to boolean true.
	 *
	 * @param \Cake\Controller\Controller $controller
	 * @return bool
	 */
	protected function _isSerializeTrue($controller) {
		if ($controller->viewBuilder()->getVar('_serialize') && $controller->viewBuilder()->getVar('_serialize') === true) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if we are using action whitelisting and if so checks if this action is whitelisted.
	 *
	 * @param \Cake\Http\ServerRequest $request The request.
	 * @return bool
	 */
	protected function _isActionEnabled(ServerRequest $request) {
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
	 * @param \Cake\Event\EventInterface $event
	 * @return void
	 */
	public function beforeRender(EventInterface $event): void {
		/** @var \Cake\Controller\Controller $controller */
		$controller = $event->getSubject();
		$controller->viewBuilder()->setClassName($this->_config['viewClass']);

		// Set flash messages to the view
		if ($this->_config['flashKey']) {
			$message = $controller->getRequest()->getSession()->consume($this->_config['flashKey']);
			if ($message || !array_key_exists('_message', $controller->viewBuilder()->getVars())) {
				$controller->set('_message', $message);
			}
		}

		// If _serialize is true, *all* viewVars will be serialized; no need to add _message.
		if ($this->_isSerializeTrue($controller)) {
			return;
		}

		$serializeKeys = ['_message'];
		if ($controller->viewBuilder()->getVar('_serialize')) {
			$serializeKeys = array_merge((array)$controller->viewBuilder()->getVar('_serialize'), $serializeKeys);
		}
		$controller->set('_serialize', $serializeKeys);
	}

}
