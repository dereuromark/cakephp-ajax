<?php

namespace Ajax\Test\TestCase\Controller\Component;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

class AjaxComponentTest extends TestCase {

	/**
	 * @var array
	 */
	public $fixtures = [
		'core.Sessions'
	];

	/**
	 * @var \Ajax\Test\TestCase\Controller\Component\AjaxComponentTestController
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

		$this->Controller = new AjaxComponentTestController(new ServerRequest(), new Response());
	}

	/**
	 * AjaxComponentTest::testNonAjax()
	 *
	 * @return void
	 */
	public function testNonAjax() {
		$this->Controller->startupProcess();
		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * AjaxComponentTest::testDefaults()
	 *
	 * @return void
	 */
	public function testDefaults() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxComponentTestController(new ServerRequest(), new Response());
		$this->Controller->components()->load('Flash');

		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);

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

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		$this->assertEquals('Ajax.Ajax', $this->Controller->viewBuilder()->getClassName());
		$this->assertEquals($expected, $this->Controller->viewVars['_message']);

		$session = $this->Controller->request->getSession()->read('Flash.flash');
		$this->assertNull($session);

		$this->Controller->redirect('/');
		$expected = [
			'Content-Type' => [
				'application/json; charset=UTF-8',
			],
		];
		$this->assertSame($expected, $this->Controller->response->getHeaders());

		$expected = [
			'url' => Router::url('/', true),
			'status' => 302,
		];
		$this->assertEquals($expected, $this->Controller->viewVars['_redirect']);
	}

	/**
	 * AjaxComponentTest::testAutoDetectOnFalse()
	 *
	 * @return void
	 */
	public function testAutoDetectOnFalse() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxComponentTestController(new ServerRequest(), new Response());

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

		$this->Controller = new AjaxComponentTestController(new ServerRequest(), new Response());

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

		$this->Controller = new AjaxComponentTestController(new ServerRequest(['params' => ['action' => 'foo']]), new Response());

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

		$this->Controller = new AjaxComponentTestController(new ServerRequest(), new Response());

		$this->Controller->components()->unload('Ajax');
		$this->Controller->components()->load('Ajax.Ajax');

		$this->Controller->startupProcess();
		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * AjaxComponentTest::testSetVars()
	 *
	 * @return void
	 */
	public function testSetVars() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxComponentTestController(new ServerRequest(), new Response());

		$this->Controller->components()->unload('Ajax');

		$content = ['id' => 1, 'title' => 'title'];
		$this->Controller->set(compact('content'));
		$this->Controller->set('_serialize', ['content']);

		$this->Controller->components()->load('Ajax.Ajax');
		$this->assertNotEmpty($this->Controller->viewVars);
		$this->assertNotEmpty($this->Controller->viewVars['_serialize']);
		$this->assertEquals('content', $this->Controller->viewVars['_serialize'][0]);
	}

	/**
	 * AjaxComponentTest::testSetVarsWithRedirect()
	 *
	 * @return void
	 */
	public function testSetVarsWithRedirect() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxComponentTestController(new ServerRequest(), new Response());
		$this->Controller->startupProcess();

		$content = ['id' => 1, 'title' => 'title'];
		$this->Controller->set(compact('content'));
		$this->Controller->set('_serialize', ['content']);

		// Let's try a permanent redirect
		$this->Controller->redirect('/', 301);
		$expected = [
			'Content-Type' => [
				'application/json; charset=UTF-8',
			],
		];
		$this->assertSame($expected, $this->Controller->response->getHeaders());

		$expected = [
			'url' => Router::url('/', true),
			'status' => 301,
		];
		$this->assertEquals($expected, $this->Controller->viewVars['_redirect']);

		$this->Controller->set(['_message' => 'test']);
		$this->Controller->redirect('/');
		$this->assertArrayHasKey('_message', $this->Controller->viewVars);

		$this->assertNotEmpty($this->Controller->viewVars);
		$this->assertNotEmpty($this->Controller->viewVars['_serialize']);
		$this->assertTrue(in_array('content', $this->Controller->viewVars['_serialize']));
	}

	/**
	 * @return void
	 */
	public function testAjaxRendering() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
	}

}

// Use Controller instead of AppController to avoid conflicts
class AjaxComponentTestController extends Controller {

	/**
	 * @var array
	 */
	public $components = ['Ajax.Ajax'];

	/**
	 * A test action
	 *
	 * @return void
	 */
	public function myTest() {
	}

}
