<?php
namespace Ajax\View;

use App\View\AppView;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Utility\Hash;

/**
 * A view to handle AJAX requests.
 *
 * Expects all incoming requests to be of extension "json" and that the expected result
 * will also be in JSON format.
 * A response to an invalid request may be just HTTP status "code" and error "message"
 * (e.g, on 4xx or 5xx).
 * A response to a valid request will always contain at least "content" and "error" keys.
 * You can add more data using _serialize.
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class AjaxView extends AppView {

	const JSON_OPTIONS = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR;

	/**
	 * List of variables to collect from the associated controller.
	 *
	 * @var array
	 */
	protected $_passedVars = [
		'viewVars', 'autoLayout', 'ext', 'helpers', 'view', 'layout', 'name', 'theme',
		'layoutPath', 'plugin', 'passedArgs', 'subDir', 'template', 'templatePath'
	];

	/**
	 * The subdirectory. AJAX views are always in ajax.
	 *
	 * @var string|null
	 */
	public $subDir = null;

	/**
	 * Name of layout to use with this View.
	 *
	 * @var bool
	 */
	public $layout = false;

	/**
	 * List of special view vars.
	 *
	 * @var array
	 */
	protected $_specialVars = ['_serialize', '_jsonOptions', '_jsonp'];

	/**
	 * Constructor
	 *
	 * @param \Cake\Http\ServerRequest|null $request Request instance.
	 * @param \Cake\Http\Response|null $response Response instance.
	 * @param \Cake\Event\EventManager|null $eventManager Event manager instance.
	 * @param array $viewOptions View options. See View::$_passedVars for list of
	 *   options which get set as class properties.
	 */
	public function __construct(
		ServerRequest $request = null,
		Response $response = null,
		EventManager $eventManager = null,
		array $viewOptions = []
	) {
		parent::__construct($request, $response, $eventManager, $viewOptions);

		if ($this->subDir === null) {
			$this->subDir = 'ajax';
			$this->templatePath = str_replace(DS . 'json', '', $this->templatePath);
			$this->templatePath = str_replace(DS . 'ajax', '', $this->templatePath);
		}

		if (isset($response)) {
			$response = $response->withType('json');
			$this->response = $response;
		}
	}

	/**
	 * Renders an AJAX view.
	 * The rendered content will be part of the JSON response object and
	 * can be accessed via response.content in JavaScript.
	 *
	 * If an error or success has been set, the rendering will be skipped.
	 *
	 * @param string|null $view The view being rendered.
	 * @param string|null $layout The layout being rendered.
	 * @return string The rendered view.
	 */
	public function render($view = null, $layout = null) {
		$dataToSerialize = [
			'error' => null,
			'success' => null,
			'content' => null,
		];

		if (isset($this->viewVars['error'])) {
			$dataToSerialize['error'] = $this->viewVars['error'];
			$view = false;
		}
		if (isset($this->viewVars['success'])) {
			$dataToSerialize['success'] = $this->viewVars['success'];
			$view = false;
		}

		if ($view !== false && !isset($this->viewVars['_redirect']) && $this->_getViewFileName($view)) {
			$dataToSerialize['content'] = parent::render($view, $layout);
		}

		$this->viewVars = Hash::merge($dataToSerialize, $this->viewVars);
		if (isset($this->viewVars['_serialize'])) {
			$dataToSerialize = $this->_dataToSerialize($this->viewVars['_serialize'], $dataToSerialize);
		}

		return $this->_serialize($dataToSerialize);
	}

	/**
	 * Serialize(json_encode) accumulated data from both our custom render method
	 *   and viewVars set by the user.
	 *
	 * @param array $dataToSerialize Array of data that is to be serialzed.
	 * @return string The serialized data.
	 */
	protected function _serialize(array $dataToSerialize = []) {
		return JsonEncoder::encode($dataToSerialize);
	}

	/**
	 * Returns data to be serialized based on the value of viewVars.
	 *
	 * @param array|string|bool $serialize The name(s) of the view variable(s) that
	 *   need(s) to be serialized. If true all available view variables will be used.
	 * @param array $additionalData Data items that were defined internally in our own
	 *   render method.
	 * @return mixed The data to serialize.
	 */
	protected function _dataToSerialize($serialize, $additionalData = []) {
		if ($serialize === true) {
			$data = array_diff_key(
				$this->viewVars,
				array_flip($this->_specialVars)
			);
			return $data;
		}

		foreach ((array)$serialize as $alias => $key) {
			if (is_numeric($alias)) {
				$alias = $key;
			}
			if (array_key_exists($key, $this->viewVars)) {
				$additionalData[$alias] = $this->viewVars[$key];
			}
		}
		return $additionalData;
	}

}
