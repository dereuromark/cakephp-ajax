<?php

namespace Ajax\Test\TestCase\Controller\Component;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use TestApp\Controller\AjaxTestController;

class AjaxComponentTest extends TestCase {

	/**
	 * @var array
	 */
	protected $fixtures = [
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
		$this->assertEquals($expected, $this->Controller->viewBuilder()->getVar('_redirect'));
	}

	/**
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
	public function testSetVars() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxTestController(new ServerRequest(), new Response());

		$this->Controller->components()->unload('Ajax');

		$content = ['id' => 1, 'title' => 'title'];
		$this->Controller->set(compact('content'));
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
		$this->Controller->set(compact('content'));
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
		$this->assertEquals($expected, $this->Controller->viewBuilder()->getVar('_redirect'));

		$this->Controller->set(['_message' => 'test']);
		$this->Controller->redirect('/');
		$this->assertArrayHasKey('_message', $this->Controller->viewBuilder()->getVars());

		$this->assertNotEmpty($this->Controller->viewBuilder()->getVars());
		$this->assertNotEmpty($this->Controller->viewBuilder()->getVar('serialize'));
		$this->assertTrue(in_array('content', $this->Controller->viewBuilder()->getVar('serialize')));
	}

}
