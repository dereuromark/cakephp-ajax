<?php

namespace Ajax\Test\TestCase\Middleware;

use Ajax\Middleware\AjaxMiddleware;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use TestApp\Controller\AjaxTestController;

class AjaxMiddlewareTest extends TestCase {

	/**
	 * @var array
	 */
	public $fixtures = [
		'core.Sessions'
	];

	/**
	 * @var \TestApp\Controller\AjaxTestController
	 */
	protected $Controller;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		Configure::write('App.namespace', 'TestApp');

		Configure::write('Ajax');
		Configure::delete('Flash');

		$request = new ServerRequest();
		$response = new Response();
		$this->Controller = new Controller($request, $response, 'Items');
	}

	/**
	 * @return void
	 */
	public function testNonAjax() {
		$next = function (RequestInterface $request, ResponseInterface $response) {
			return $this->Controller->render('index');
		};

		$middleware = new AjaxMiddleware();
		$result = $middleware($this->Controller->request, $this->Controller->response, $next);

		$this->assertInstanceOf(ResponseInterface::class, $result);
		$this->assertEquals(200, $result->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'text/html; charset=UTF-8',
			],
		];
		$this->assertSame($expectedHeaders, $result->getHeaders());

		$this->assertEquals('My Index Test ctp', $result->getBody());
	}

	/**
	 * @return void
	 */
	public function testDefaultsRender() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller->components()->load('Flash');

		$this->Controller->components()->Flash->custom('A message');
		$session = $this->Controller->request->getSession()->read('Flash.flash');
		$expected = [
			[
				'message' => 'A message',
				'key' => 'flash',
				'element' => 'Flash/custom',
				'params' => []
			]
		];
		$this->assertEquals($expected, $session);

		$next = function (RequestInterface $request, ResponseInterface $response) {
			return $this->Controller->render('index');
		};

		$middleware = new AjaxMiddleware();
		$result = $middleware($this->Controller->request, $this->Controller->response, $next);

		$this->assertInstanceOf(ResponseInterface::class, $result);
		$this->assertEquals(200, $result->getStatusCode());

		$this->assertEquals('Ajax.Ajax', $this->Controller->viewBuilder()->getClassName());
		$this->assertEquals($expected, $this->Controller->viewVars['_message']);

		$session = $this->Controller->request->getSession()->read('Flash.flash');
		$this->assertNull($session);

		$expectedHeaders = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expectedHeaders, $result->getHeaders());

		$expected = [
			'error' => null,
			'success' => null,
			'content' => 'My Ajax Index Test ctp',
			'_message' => $expected,
		];
		$this->assertEquals(json_encode($expected), (string)$result->getBody());
	}

	/**
	 * @return void
	 */
	public function testDefaultsRedirect() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$next = function (RequestInterface $request, ResponseInterface $response) {
			return $this->Controller->redirect('/');
		};

		$middleware = new AjaxMiddleware();
		$result = $middleware($this->Controller->request, $this->Controller->response, $next);

		$this->assertInstanceOf(ResponseInterface::class, $result);
		$this->assertEquals(200, $result->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expectedHeaders, $result->getHeaders());

		$expected = [
			'error' => null,
			'content' => null,
			'_message' => null,
			'_redirect' => [
				'url' => Router::url('/', true),
				'status' => 302,
			],
		];
		$this->assertEquals(json_encode($expected), (string)$result->getBody());
	}

	/**
	 * @return void
	 */
	public function testAutoDetectOnFalse() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$next = function (RequestInterface $request, ResponseInterface $response) {
			return $this->Controller->render('index');
		};

		$middleware = new AjaxMiddleware(['autoDetect' => false]);
		$result = $middleware($this->Controller->request, $this->Controller->response, $next);

		$this->assertInstanceOf(ResponseInterface::class, $result);
		$this->assertEquals(200, $result->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'text/html; charset=UTF-8',
			],
		];
		$this->assertSame($expectedHeaders, $result->getHeaders());

		$this->assertEquals('My Index Test ctp', $result->getBody());
	}

	/**
	 * @return void
	 */
	public function testActionsInvalid() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.actions', ['foo']);

		$next = function (RequestInterface $request, ResponseInterface $response) {
			return $this->Controller->render('index');
		};

		$middleware = new AjaxMiddleware();
		$result = $middleware($this->Controller->request, $this->Controller->response, $next);

		$this->assertInstanceOf(ResponseInterface::class, $result);
		$this->assertEquals(200, $result->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'text/html; charset=UTF-8',
			],
		];
		$this->assertSame($expectedHeaders, $result->getHeaders());

		$this->assertEquals('My Index Test ctp', $result->getBody());
	}

	/**
	 * @return void
	 */
	public function testActions() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.actions', ['foo']);

		$request = new ServerRequest(['params' => ['action' => 'foo']]);
		$response = new Response();
		$this->Controller = new AjaxTestController($request, $response, 'Items');

		$next = function (RequestInterface $request, ResponseInterface $response) {
			return $this->Controller->render('index');
		};

		$middleware = new AjaxMiddleware();
		$result = $middleware($this->Controller->request, $this->Controller->response, $next);

		$this->assertInstanceOf(ResponseInterface::class, $result);
		$this->assertEquals(200, $result->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expectedHeaders, $result->getHeaders());

		$expected = [
			'error' => null,
			'success' => null,
			'content' => 'My Ajax Index Test ctp',
			'_message' => null,
		];
		$this->assertEquals(json_encode($expected), (string)$result->getBody());
	}

	/**
	 * @return void
	 */
	public function testAutoDetectOnFalseViaConfig() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.autoDetect', false);

		$next = function (RequestInterface $request, ResponseInterface $response) {
			return $this->Controller->render('index');
		};

		$middleware = new AjaxMiddleware();
		$result = $middleware($this->Controller->request, $this->Controller->response, $next);

		$this->assertInstanceOf(ResponseInterface::class, $result);
		$this->assertEquals(200, $result->getStatusCode());

		$this->assertEquals('My Index Test ctp', $result->getBody());
	}

	/**
	 * @return void
	 */
	public function testSetVars() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$next = function (RequestInterface $request, ResponseInterface $response) {
			$content = ['id' => 1, 'title' => 'title'];
			$this->Controller->set(compact('content'));
			$this->Controller->set('_serialize', ['content']);

			return $this->Controller->render('index');
		};

		$middleware = new AjaxMiddleware();
		$result = $middleware($this->Controller->request, $this->Controller->response, $next);

		$this->assertNotEmpty($this->Controller->viewVars);
		$this->assertNotEmpty($this->Controller->viewVars['_serialize']);

		$this->assertInstanceOf(ResponseInterface::class, $result);
		$this->assertEquals(200, $result->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expectedHeaders, $result->getHeaders());

		$this->assertEquals('content', $this->Controller->viewVars['_serialize'][0]);

		$expected = [
			'error' => null,
			'success' => null,
			'content' => $this->Controller->viewVars['content'],
			'_message' => null,
		];
		$this->assertEquals(json_encode($expected), (string)$result->getBody());
	}

}
