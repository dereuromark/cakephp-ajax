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
use TestApp\Http\TestRequestHandler;

class AjaxMiddlewareTest extends TestCase {

	/**
	 * @var array
	 */
	public $fixtures = [
		'core.Sessions',
	];

	/**
	 * @var \TestApp\Controller\AjaxTestController
	 */
	protected $Controller;

	/**
	 * @return void
	 */
	public function setUp(): void {
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

		$request = $this->Controller->getRequest();
		$response = $this->Controller->getResponse();
		$handler = new TestRequestHandler(null, $response);
		$newResponse = $middleware->process($request, $handler);

		$this->assertInstanceOf(ResponseInterface::class, $newResponse);
		$this->assertEquals(200, $newResponse->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'text/html; charset=UTF-8',
			],
		];
		$this->assertSame($expectedHeaders, $newResponse->getHeaders());

		$this->assertTextContains('My Index Test ctp', $newResponse->getBody());
	}

	/**
	 * @return void
	 */
	public function testDefaultsRender() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller->components()->load('Flash');

		$this->Controller->components()->Flash->custom('A message');
		$session = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$expected = [
			[
				'message' => 'A message',
				'key' => 'flash',
				'element' => 'flash/custom',
				'params' => [],
			],
		];
		$this->assertEquals($expected, $session);

		$next = function (RequestInterface $request, ResponseInterface $response) {
			return $this->Controller->render('index');
		};

		$middleware = new AjaxMiddleware();

		$request = $this->Controller->getRequest();
		$response = $this->Controller->getResponse();
		$handler = new TestRequestHandler(null, $response);
		$newResponse = $middleware->process($request, $handler);

		$this->assertInstanceOf(ResponseInterface::class, $newResponse);
		$this->assertEquals(200, $newResponse->getStatusCode());

		$this->assertEquals('Ajax.Ajax', $this->Controller->viewBuilder()->getClassName());
		$this->assertEquals($expected, $this->Controller->viewBuilder()->getVar('_message'));

		$session = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$this->assertNull($session);

		$expectedHeaders = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expectedHeaders, $newResponse->getHeaders());

		$expected = [
			'error' => null,
			'success' => null,
			'content' => 'My Ajax Index Test ctp' . PHP_EOL,
			'_message' => $expected,
		];
		$this->assertEquals(json_encode($expected), (string)$newResponse->getBody());
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
		$request = $this->Controller->getRequest();
		$response = $this->Controller->getResponse();
		$handler = new TestRequestHandler(null, $response);
		$newResponse = $middleware->process($request, $handler);

		$this->assertInstanceOf(ResponseInterface::class, $newResponse);
		$this->assertEquals(200, $newResponse->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expectedHeaders, $newResponse->getHeaders());

		$expected = [
			'error' => null,
			'content' => null,
			'_message' => null,
			'_redirect' => [
				'url' => Router::url('/', true),
				'status' => 302,
			],
		];
		$this->assertEquals(json_encode($expected), (string)$newResponse->getBody());
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
		$request = $this->Controller->getRequest();
		$response = $this->Controller->getResponse();
		$handler = new TestRequestHandler(null, $response);
		$newResponse = $middleware->process($request, $handler);

		$this->assertInstanceOf(ResponseInterface::class, $newResponse);
		$this->assertEquals(200, $newResponse->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'text/html; charset=UTF-8',
			],
		];
		$this->assertSame($expectedHeaders, $newResponse->getHeaders());

		$this->assertTextContains('My Index Test ctp', $newResponse->getBody());
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
		$request = $this->Controller->getRequest();
		$response = $this->Controller->getResponse();
		$handler = new TestRequestHandler(null, $response);
		$newResponse = $middleware->process($request, $handler);

		$this->assertInstanceOf(ResponseInterface::class, $newResponse);
		$this->assertEquals(200, $newResponse->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'text/html; charset=UTF-8',
			],
		];
		$this->assertSame($expectedHeaders, $newResponse->getHeaders());

		$this->assertTextContains('My Index Test ctp', $newResponse->getBody());
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
		$request = $this->Controller->getRequest();
		$response = $this->Controller->getResponse();
		$handler = new TestRequestHandler(null, $response);
		$newResponse = $middleware->process($request, $handler);

		$this->assertInstanceOf(ResponseInterface::class, $newResponse);
		$this->assertEquals(200, $newResponse->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expectedHeaders, $newResponse->getHeaders());

		$expected = [
			'error' => null,
			'success' => null,
			'content' => 'My Ajax Index Test ctp' . PHP_EOL,
			'_message' => null,
		];
		$this->assertEquals(json_encode($expected), (string)$newResponse->getBody());
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
		$request = $this->Controller->getRequest();
		$response = $this->Controller->getResponse();
		$handler = new TestRequestHandler(null, $response);
		$newResponse = $middleware->process($request, $handler);

		$this->assertInstanceOf(ResponseInterface::class, $newResponse);
		$this->assertEquals(200, $newResponse->getStatusCode());

		$this->assertTextContains('My Index Test ctp', $newResponse->getBody());
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
		$request = $this->Controller->getRequest();
		$response = $this->Controller->getResponse();
		$handler = new TestRequestHandler(null, $response);
		$newResponse = $middleware->process($request, $handler);

		$this->assertNotEmpty($this->Controller->viewBuilder()->getVars());
		$this->assertNotEmpty($this->Controller->viewBuilder()->getVar('_serialize'));

		$this->assertInstanceOf(ResponseInterface::class, $newResponse);
		$this->assertEquals(200, $newResponse->getStatusCode());

		$expectedHeaders = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expectedHeaders, $newResponse->getHeaders());

		$this->assertEquals('content', $this->Controller->viewBuilder()->getVar('_serialize')[0]);

		$expected = [
			'error' => null,
			'success' => null,
			'content' => $this->Controller->viewBuilder()->getVar('content'),
			'_message' => null,
		];
		$this->assertEquals(json_encode($expected), (string)$newResponse->getBody());
	}

}
