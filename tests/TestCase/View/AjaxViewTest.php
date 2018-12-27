<?php
/**
 * PHP 5
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @author        Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
namespace Ajax\Test\TestCase\View;

use Ajax\View\AjaxView;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * AjaxViewTest
 */
class AjaxViewTest extends TestCase {

	/**
	 * @var \Ajax\View\AjaxView
	 */
	public $Ajax;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		Configure::write('App.namespace', 'TestApp');

		$this->Ajax = new AjaxView();
	}

	/**
	 * @return void
	 */
	public function testSerialize() {
		$Request = new ServerRequest();
		$Response = new Response();
		$items = [
			['title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'],
			['title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'],
		];
		$View = new AjaxView($Request, $Response);
		$View->set(['items' => $items, '_serialize' => ['items']]);
		$result = $View->render(false);

		$response = $View->response;
		$this->assertSame('application/json', $response->getType());
		$expected = ['error' => null, 'content' => null, 'items' => $items];
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testRenderWithSerialize() {
		$Request = new ServerRequest();
		$Response = new Response();
		$items = [
			['title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'],
			['title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'],
		];
		$View = new AjaxView($Request, $Response);
		$View->set(['items' => $items, '_serialize' => 'items']);
		$View->setTemplatePath('Items');
		$result = $View->render('index');

		$response = $View->response;
		$this->assertSame('application/json', $response->getType());
		$expected = ['error' => null, 'content' => 'My Ajax Index Test ctp', 'items' => $items];
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * AjaxViewTest::testSerializeSetTrue()
	 *
	 * Test the case where the _serialize viewVar is set to true signaling that all viewVars
	 *   should be serialized.
	 *
	 * @return void
	 */
	public function testSerializeSetTrue() {
		$Request = new ServerRequest();
		$Response = new Response();
		$items = [
			['title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'],
			['title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'],
		];
		$multiple = 'items';
		$View = new AjaxView($Request, $Response);
		$View->set(['items' => $items, 'multiple' => $multiple, '_serialize' => true]);
		$result = $View->render(false);

		$response = $View->response;
		$this->assertSame('application/json', $response->getType());
		$expected = ['error' => null, 'content' => null, 'items' => $items, 'multiple' => $multiple];
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testError() {
		$Request = new ServerRequest();
		$Response = new Response();
		$items = [
			['title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'],
			['title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'],
		];
		$View = new AjaxView($Request, $Response);
		$View->set(['error' => 'Some message', 'items' => $items, '_serialize' => ['error', 'items']]);
		$View->setTemplatePath('Items');
		$result = $View->render('index');

		$response = $View->response;
		$this->assertSame('application/json', $response->getType());
		$expected = ['error' => 'Some message', 'content' => null, 'items' => $items];
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * AjaxViewTest::testWithoutSubdir()
	 *
	 * @return void
	 */
	public function testWithoutSubdir() {
		$Request = new ServerRequest();
		$Response = new Response();
		$Controller = new Controller($Request, $Response);
		$View = new AjaxView($Request, $Response);
		$View->setTemplatePath('Items');
		$View->subDir = false;
		$result = $View->render('index');

		$response = $View->response;
		$this->assertSame('application/json', $response->getType());
		$expected = ['error' => null, 'content' => 'My Index Test ctp'];
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function _testRender() {
		$Request = new ServerRequest();
		$Response = new Response();
		$Controller = new AjaxComponentTestController($Request, $Response);

		$Controller->viewBuilder()->setClassName('Ajax.Ajax');
		$Controller->viewBuilder()->setTemplate('myTest');
		$Controller->viewBuilder()->setTemplatePath('AjaxComponentTest');
		$Controller->myTest();

		//$Controller->subDir = false;
		$result = $Controller->render();

		$this->assertSame('application/json', $Response->getType());
		$expected = ['error' => null, 'content' => 'My Ajax Index Test ctp'];
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
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
