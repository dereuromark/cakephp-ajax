<?php

namespace Ajax\Test\TestCase\Controller\Component;

use App\Model\AppModel;
use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Ajax\Controller\Component\AjaxComponent;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Event\Event;

/**
 */
class AjaxComponentTest extends TestCase {

	public $fixtures = array(
		'core.Sessions'
	);

	public function setUp() {
		parent::setUp();

		Configure::write('App.namespace', 'TestApp');

		Configure::write('Ajax');
		Configure::delete('Flash');

		$this->Controller = new AjaxComponentTestController(new Request(), new Response());
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

		$this->Controller = new AjaxComponentTestController(new Request(), new Response());
		$this->Controller->components()->load('Flash');

		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);

		$this->Controller->components()->Flash->custom('A message');
		$session = $this->Controller->request->session()->read('Flash.flash');
		$expected = array(
			'message' => 'A message',
			'key' => 'flash',
			'element' => 'Flash/custom',
			'params' => array()
		);
		$this->assertEquals($expected, $session);

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);

		$this->assertEquals('Ajax.Ajax', $this->Controller->viewClass);
		$this->assertEquals($expected, $this->Controller->viewVars['_message']);

		$session = $this->Controller->request->session()->read('Flash.flash');
		$this->assertNull($session);

		$this->Controller->redirect('/');
		$this->assertSame(array(), $this->Controller->response->header());

		$expected = array(
			'url' => Router::url('/', true),
			'status' => 302,
		);
		$this->assertEquals($expected, $this->Controller->viewVars['_redirect']);
	}

	/**
	 * AjaxComponentTest::testAutoDetectOnFalse()
	 *
	 * @return void
	 */
	public function testAutoDetectOnFalse() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxComponentTestController(new Request(), new Response());

		$this->Controller->components()->unload('Ajax');
		$this->Controller->components()->load('Ajax.Ajax', array('autoDetect' => false));

		$this->Controller->startupProcess();
		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * AjaxComponentTest::testAutoDetectOnFalseViaConfig()
	 *
	 * @return void
	 */
	public function testAutoDetectOnFalseViaConfig() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.autoDetect', false);

		$this->Controller = new AjaxComponentTestController(new Request(), new Response());

		$this->Controller->components()->unload('Ajax');
		$this->Controller->components()->load('Ajax.Ajax');

		$this->Controller->startupProcess();
		$this->assertFalse($this->Controller->components()->Ajax->respondAsAjax);
	}

	/**
	 * AjaxComponentTest::testToolsMultiMessages()
	 *
	 * @return void
	 */
	public function testToolsMultiMessages() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Configure::write('Ajax.flashKey', 'FlashMessage');

		$this->Controller = new AjaxComponentTestController(new Request(), new Response());
		$this->Controller->components()->load('Tools.Flash');

		$this->Controller->components()->unload('Ajax');
		$this->Controller->components()->load('Ajax.Ajax');


		$this->Controller->startupProcess();
		$this->assertTrue($this->Controller->components()->Ajax->respondAsAjax);

		$this->Controller->components()->Flash->message('A message', 'success');
		$session = $this->Controller->request->session()->read('FlashMessage');
		$expected = array(
			'success' => array('A message')
		);
		$this->assertEquals($expected, $session);

		$event = new Event('Controller.beforeRender');
		$this->Controller->components()->Ajax->beforeRender($event);
		$this->assertEquals('Ajax.Ajax', $this->Controller->viewClass);

		$this->assertEquals($expected, $this->Controller->viewVars['_message']);

		$session = $this->Controller->request->session()->read('FlashMessage');
		$this->assertNull($session);
	}

	/**
	 * AjaxComponentTest::testSetVars()
	 *
	 * @return void
	 */
	public function testSetVars() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller = new AjaxComponentTestController(new Request(), new Response());

		$this->Controller->components()->unload('Ajax');

		$content = array('id' => 1, 'title' => 'title');
		$this->Controller->set(compact('content'));
		$this->Controller->set('_serialize', array('content'));

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

		$this->Controller = new AjaxComponentTestController(new Request(), new Response());
		$this->Controller->startupProcess();

		$content = array('id' => 1, 'title' => 'title');
		$this->Controller->set(compact('content'));
		$this->Controller->set('_serialize', array('content'));

		// Let's try a permanent redirect
		$this->Controller->redirect('/', 301);
		$this->assertSame(array(), $this->Controller->response->header());

		$expected = array(
			'url' => Router::url('/', true),
			'status' => 301,
		);
		$this->assertEquals($expected, $this->Controller->viewVars['_redirect']);

		$this->Controller->set(array('_message' => 'test'));
		$this->Controller->redirect('/');
		$this->assertArrayHasKey('_message', $this->Controller->viewVars);

		$this->assertNotEmpty($this->Controller->viewVars);
		$this->assertNotEmpty($this->Controller->viewVars['_serialize']);
		$this->assertTrue(in_array('content', $this->Controller->viewVars['_serialize']));
	}
}

// Use Controller instead of AppController to avoid conflicts
class AjaxComponentTestController extends Controller {

	public $components = array('Ajax.Ajax');

}
