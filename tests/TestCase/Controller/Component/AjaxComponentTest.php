<?php

namespace Ajax\Test\TestCase\Controller\Component;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use TestApp\Controller\AjaxTestController;

class AjaxComponentTest extends TestCase {

	/**
	 * @var array
	 */
	protected array $fixtures = [
		//'core.Sessions',
	];

	/**
	 * @var \TestApp\Controller\AjaxTestController
	 */
	protected AjaxTestController $Controller;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		Configure::write('App.namespace', 'TestApp');

		Configure::write('Ajax');
		Configure::delete('Flash');

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
	}

	/**
	 * @return void
	 */
	public function testNonAjax() {
		$this->Controller->startupProcess();
		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * @throws \Exception
	 * @return void
	 */
	public function testDefaults() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
		$this->Controller->components()->load('Flash');

		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);

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

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		$this->assertEquals('Ajax.Ajax', $this->Controller->viewBuilder()->getClassName());
		$this->assertEquals($expected, $this->Controller->viewBuilder()->getVar('_message'));

		$session = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$this->assertNull($session);

		$this->Controller->redirect('/');
		$expected = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expected, $this->Controller->getResponse()->getHeaders());

		$expected = [
			'url' => Router::url('/', true),
			'status' => 302,
		];
		$this->assertSame($expected, $this->Controller->viewBuilder()->getVar('_redirect'));
	}

	/**
	 * @throws \Exception
	 * @return void
	 */
	public function testAutoDetectOnFalse() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());

		$this->Controller->components()->unload('Ajax');
		$this->Controller->components()->load('Ajax.Ajax', ['autoDetect' => false]);

		$this->Controller->startupProcess();
		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * @throws \Exception
	 * @return void
	 */
	public function testActionsInvalid() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.actions', ['foo']);

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());

		$this->Controller->components()->unload('Ajax');
		$this->Controller->components()->load('Ajax.Ajax');

		$this->Controller->startupProcess();
		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * @throws \Exception
	 * @return void
	 */
	public function testActions() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.actions', ['foo']);

		$this->Controller = new AjaxTestController(new ServerRequest(['params' => ['action' => 'foo']]), new Response());

		$this->Controller->components()->unload('Ajax');
		$this->Controller->components()->load('Ajax.Ajax');

		$this->Controller->startupProcess();
		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * @throws \Exception
	 * @return void
	 */
	public function testAutoDetectOnFalseViaConfig() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.autoDetect', false);

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());

		$this->Controller->components()->unload('Ajax');
		$this->Controller->components()->load('Ajax.Ajax');

		$this->Controller->startupProcess();
		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * @return void
	 */
	public function testAcceptJsonDoesNotEnableByDefault() {
		$request = new ServerRequest([
			'environment' => ['HTTP_ACCEPT' => 'application/json'],
		]);
		$this->Controller = new AjaxTestController($request, new Response());

		$this->Controller->startupProcess();

		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * @return void
	 */
	public function testAcceptJsonDetector() {
		Configure::write('Ajax.detectors', ['ajax', 'acceptJson']);
		$request = new ServerRequest([
			'environment' => ['HTTP_ACCEPT' => 'application/json, text/plain;q=0.5'],
		]);
		$this->Controller = new AjaxTestController($request, new Response());

		$this->Controller->startupProcess();

		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * @return void
	 */
	public function testAcceptJsonDetectorWithVendorMediaType() {
		Configure::write('Ajax.detectors', ['acceptJson']);
		$request = new ServerRequest([
			'environment' => ['HTTP_ACCEPT' => 'application/vnd.api+json'],
		]);
		$this->Controller = new AjaxTestController($request, new Response());

		$this->Controller->startupProcess();

		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * @return void
	 */
	public function testJsonExtensionDetector() {
		Configure::write('Ajax.detectors', ['jsonExtension']);
		$request = new ServerRequest([
			'params' => ['_ext' => 'json'],
		]);
		$this->Controller = new AjaxTestController($request, new Response());

		$this->Controller->startupProcess();

		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * @throws \Exception
	 * @return void
	 */
	public function testSetVars() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());

		$this->Controller->components()->unload('Ajax');

		$content = ['id' => 1, 'title' => 'title'];
		$this->Controller->set(['content' => $content]);
		$this->Controller->set('serialize', ['content']);

		$this->Controller->components()->load('Ajax.Ajax');
		$this->assertNotEmpty($this->Controller->viewBuilder()->getVars());
		$this->assertNotEmpty($this->Controller->viewBuilder()->getVar('serialize'));
		$this->assertEquals('content', $this->Controller->viewBuilder()->getVar('serialize')[0]);
	}

	/**
	 * @return void
	 */
	public function testSetVarsWithRedirect() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
		$this->Controller->startupProcess();

		$content = ['id' => 1, 'title' => 'title'];
		$this->Controller->set(['content' => $content]);
		$this->Controller->set('serialize', ['content']);

		// Let's try a permanent redirect
		$this->Controller->redirect('/', 301);
		$expected = [
			'Content-Type' => [
				'application/json',
			],
		];
		$this->assertSame($expected, $this->Controller->getResponse()->getHeaders());

		$expected = [
			'url' => Router::url('/', true),
			'status' => 301,
		];
		$this->assertSame($expected, $this->Controller->viewBuilder()->getVar('_redirect'));

		$this->Controller->set(['_message' => 'test']);
		$this->Controller->redirect('/');
		$this->assertArrayHasKey('_message', $this->Controller->viewBuilder()->getVars());

		$this->assertNotEmpty($this->Controller->viewBuilder()->getVars());
		$this->assertNotEmpty($this->Controller->viewBuilder()->getVar('serialize'));
		$this->assertTrue(in_array('content', $this->Controller->viewBuilder()->getVar('serialize')));
	}

	/**
	 * enable() must force AJAX handling on for non-XHR requests too.
	 *
	 * @return void
	 */
	public function testEnableForcesAjaxOnNonXhr() {
		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
		$this->Controller->startupProcess();

		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);

		$this->Controller->components()->Ajax->enable();
		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		$this->assertSame('Ajax.Ajax', $this->Controller->viewBuilder()->getClassName());
	}

	/**
	 * disable() must force AJAX handling off even when X-Requested-With is set.
	 *
	 * @return void
	 */
	public function testDisableForcesAjaxOffOnXhr() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
		$this->Controller->startupProcess();

		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);

		$this->Controller->components()->Ajax->disable();
		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);

		// beforeRender must short-circuit and leave the view class alone
		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);
		$this->assertNotSame('Ajax.Ajax', $this->Controller->viewBuilder()->getClassName());
	}

	/**
	 * Calling enable() before initialize() must survive autoDetect overwriting respondAsAjax.
	 *
	 * @return void
	 */
	public function testEnableBeforeInitializeWins() {
		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());

		$this->Controller->components()->unload('Ajax');
		$this->Controller->components()->load('Ajax.Ajax');
		$this->Controller->components()->Ajax->enable();

		// Re-run initialize to simulate the lifecycle (defensive: forced should still win)
		$this->Controller->components()->Ajax->initialize([]);

		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * flashKey defaults must auto-resolve from a loaded FlashComponent's `key` config.
	 *
	 * @return void
	 */
	public function testFlashKeyAutoResolvesFromFlashComponent() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
		$this->Controller->components()->load('Flash', ['key' => 'myStack']);

		$this->Controller->getRequest()->getSession()->write('Flash.myStack', [
			['message' => 'Saved', 'key' => 'myStack', 'element' => 'flash/success', 'params' => []],
		]);

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		$message = $this->Controller->viewBuilder()->getVar('_message');
		$this->assertIsArray($message);
		$this->assertSame('Saved', Hash::get($message, '0.message'));

		// Default behavior is destructive consume.
		$this->assertNull($this->Controller->getRequest()->getSession()->read('Flash.myStack'));
	}

	/**
	 * flashKey explicitly set to false suppresses _message entirely (lets FlashHelper render later).
	 *
	 * @return void
	 */
	public function testFlashKeyFalseSuppressesMessage() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.flashKey', false);

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
		$this->Controller->components()->load('Flash');
		$this->Controller->components()->Flash->success('Hello');

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		$this->assertNull($this->Controller->viewBuilder()->getVar('_message'));
		// And the flash is left intact for the helper.
		$this->assertNotNull($this->Controller->getRequest()->getSession()->read('Flash.flash'));
	}

	/**
	 * flashConsumer callable can implement non-destructive read or shape transformation.
	 *
	 * @return void
	 */
	public function testFlashConsumerCallable() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.flashConsumer', function (ServerRequest $req, string $key): ?string {
			$messages = $req->getSession()->consume($key);
			if (!is_array($messages) || !$messages) {
				return null;
			}

			return (string)($messages[0]['message'] ?? '');
		});

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
		$this->Controller->components()->load('Flash');
		$this->Controller->components()->Flash->error('Boom');

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		// Consumer normalized the array stack down to a single string.
		$this->assertSame('Boom', $this->Controller->viewBuilder()->getVar('_message'));
	}

	/**
	 * No FlashComponent loaded and no flash data in session: _message must not be set.
	 *
	 * @return void
	 */
	public function testNoFlashDataDoesNotSetMessage() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		$this->assertSame('Ajax.Ajax', $this->Controller->viewBuilder()->getClassName());
		$this->assertNull($this->Controller->viewBuilder()->getVar('_message'));
	}

	/**
	 * Auto-resolve must work with a non-cake-core Flash component (e.g. dereuromark/cakephp-flash),
	 * which extends `Cake\Controller\Component` directly rather than the core `FlashComponent`.
	 *
	 * @return void
	 */
	public function testFlashKeyAutoResolvesFromCustomFlashComponent() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
		$this->Controller->components()->load('Flash', [
			'className' => 'TestApp\Controller\Component\CustomFlashComponent',
			'key' => 'myStack',
		]);

		$this->Controller->getRequest()->getSession()->write('Flash.myStack', [
			['message' => 'Saved', 'key' => 'myStack', 'element' => 'flash/success', 'params' => []],
		]);

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		$message = $this->Controller->viewBuilder()->getVar('_message');
		$this->assertIsArray($message);
		$this->assertSame('Saved', Hash::get($message, '0.message'));
	}

	/**
	 * BC: legacy explicit flashKey string still wins over the auto-resolve path.
	 *
	 * @return void
	 */
	public function testLegacyExplicitFlashKey() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.flashKey', 'Flash.legacy');

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());
		// FlashComponent's own key is `flash`, but the explicit override wins.
		$this->Controller->components()->load('Flash');
		$this->Controller->getRequest()->getSession()->write('Flash.legacy', ['legacy data']);

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		$this->assertSame(['legacy data'], $this->Controller->viewBuilder()->getVar('_message'));
	}

}
