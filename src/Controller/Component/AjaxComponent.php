<?php

namespace Ajax\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Event\Event;
use Cake\Network\Response;

/**
 * Ajax Component to respond to AJAX requests.
 *
 * Works together with the AjaxView to easily switch
 * output type from HTML to JSON format.
 *
 * It will also avoid redirects and pass those down as content
 * of the JSON response object.
 *
 * Don't forget Configure::write('Ajax.flashKey', 'messages');
 * if you want to use it with Tools.Flash component.
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class AjaxComponent extends Component {

	public $Controller;

	public $respondAsAjax = false;

	protected $_defaultConfig = array(
		'viewClass' => 'Ajax.Ajax',
		'autoDetect' => true,
		'resolveRedirect' => true,
		'flashKey' => 'Flash.flash' // Use "messages" for Tools plugin Flash component, set to false to disable
	);

	/**
	 * Constructor.
	 *
	 * @param ComponentRegistry $collection
	 * @param array $config
	 */
	public function __construct(ComponentRegistry $collection, $config = array()) {
		$this->Controller = $collection->getController();

		$defaults = (array)Configure::read('Ajax') + $this->_defaultConfig;
		$config += $defaults;
		parent::__construct($collection, $config);
	}

	public function initialize(array $config = array()) {
		if (!$this->_config['autoDetect']) {
			return;
		}
		$this->respondAsAjax = $this->Controller->request->is('ajax');
	}

	/**
	 * Called before the Controller::beforeRender(), and before
	 * the view class is loaded, and before Controller::render()
	 *
	 * @param Controller $controller Controller with components to beforeRender
	 * @return void
	 */
	public function beforeRender(Event $event) {
		if (!$this->respondAsAjax) {
			return;
		}

		$this->_respondAsAjax();
	}

	/**
	 * AjaxComponent::respondAsAjax()
	 *
	 * @return void
	 */
	protected function _respondAsAjax() {
		$this->Controller->viewBuilder()->className($this->_config['viewClass']);

		// Set flash messages to the view
		if ($this->_config['flashKey']) {
			$message = $this->Controller->request->session()->consume($this->_config['flashKey']);
			$this->Controller->set('_message', $message);
		}
	}

	/**
	 * Called before Controller::redirect(). Allows you to replace the URL that will
	 * be redirected to with a new URL.
	 *
	 * @param Event $event Event
	 * @param string|array $url Either the string or URL array that is being redirected to.
	 * @param Response $response
	 * @return void
	 */
	public function beforeRedirect(Event $event, $url, Response $response) {
		if (!$this->respondAsAjax || !$this->_config['resolveRedirect']) {
			return;
		}

		$url = Router::url($url, true);

		$status = $response->statusCode();
		$response->statusCode(200);

		$this->Controller->autoRender = true;
		$this->Controller->set('_redirect', compact('url', 'status'));
		$serializeKeys = array('_redirect', '_message');
		if (!empty($this->Controller->viewVars['_serialize'])) {
			$serializeKeys = array_merge($serializeKeys, $this->Controller->viewVars['_serialize']);
		}
		$this->Controller->set('_serialize', $serializeKeys);
		$event->stopPropagation();
	}

}
