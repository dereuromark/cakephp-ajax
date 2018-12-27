<?php

namespace Ajax\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Routing\Router;

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
	 * @var \Cake\Controller\Controller
	 */
	public $Controller;

	/**
	 * @var bool
	 */
	public $respondAsAjax = false;

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'viewClass' => 'Ajax.Ajax',
		'autoDetect' => true,
		'resolveRedirect' => true,
		'flashKey' => 'Flash.flash',
		'actions' => [],
	];

	/**
	 * @param \Cake\Controller\ComponentRegistry $collection
	 * @param array $config
	 */
	public function __construct(ComponentRegistry $collection, $config = []) {
		$this->Controller = $collection->getController();

		$defaults = (array)Configure::read('Ajax') + $this->_defaultConfig;
		$config += $defaults;
		parent::__construct($collection, $config);
	}

	/**
	 * @param array $config
	 * @return void
	 */
	public function initialize(array $config = []) {
		if (!$this->_config['autoDetect'] || !$this->_isActionEnabled()) {
			return;
		}
		$this->respondAsAjax = $this->Controller->request->is('ajax');
	}

	/**
	 * Called before the Controller::beforeRender(), and before
	 * the view class is loaded, and before Controller::render()
	 *
	 * @param \Cake\Event\Event $event
	 * @return void
	 */
	public function beforeRender(Event $event) {
		if (!$this->respondAsAjax) {
			return;
		}

		$this->_respondAsAjax();
	}

	/**
	 * @return void
	 */
	protected function _respondAsAjax() {
		$this->Controller->viewBuilder()->setClassName($this->_config['viewClass']);

		// Set flash messages to the view
		if ($this->_config['flashKey']) {
			$message = $this->Controller->request->getSession()->consume($this->_config['flashKey']);
			$this->Controller->set('_message', $message);
		}

		// If _serialize is true, *all* viewVars will be serialized; no need to add _message.
		if ($this->_isControllerSerializeTrue()) {
			return;
		}

		$serializeKeys = ['_message'];
		if (!empty($this->Controller->viewVars['_serialize'])) {
			$serializeKeys = array_merge($serializeKeys, (array)$this->Controller->viewVars['_serialize']);
		}
		$this->Controller->set('_serialize', $serializeKeys);
	}

	/**
	 * Called before Controller::redirect(). Allows you to replace the URL that will
	 * be redirected to with a new URL.
	 *
	 * @param \Cake\Event\Event $event Event
	 * @param string|array $url Either the string or URL array that is being redirected to.
	 * @param \Cake\Http\Response $response
	 * @return \Cake\Http\Response|null
	 */
	public function beforeRedirect(Event $event, $url, Response $response) {
		if (!$this->respondAsAjax || !$this->_config['resolveRedirect']) {
			return null;
		}

		$url = Router::url($url, true);

		$status = $response->getStatusCode();
		$response = $response->withStatus(200)->withoutHeader('Location');
		$this->Controller->setResponse($response);

		$this->Controller->enableAutoRender();
		$this->Controller->set('_redirect', compact('url', 'status'));

		$event->stopPropagation();

		if ($this->_isControllerSerializeTrue()) {
			return null;
		}

		$serializeKeys = ['_redirect'];
		if (!empty($this->Controller->viewVars['_serialize'])) {
			$serializeKeys = array_merge($serializeKeys, (array)$this->Controller->viewVars['_serialize']);
		}
		$this->Controller->set('_serialize', $serializeKeys);
		// Further changes will be required here when the change to immutable response objects is completed
		$response = $this->Controller->render();

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
	protected function _isActionEnabled() {
		$actions = $this->getConfig('actions');
		if (!$actions) {
			return true;
		}

		return in_array($this->getController()->getRequest()->getParam('action'), $actions, true);
	}

}
