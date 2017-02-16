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
use Cake\Network\Request;
use Cake\Network\Response;
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
	 * AjaxViewTest::setUp()
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		Configure::write('App.namespace', 'TestApp');

		$this->Ajax = new AjaxView();
	}

	/**
	 * AjaxViewTest::testSerialize()
	 *
	 * @return void
	 */
	public function testSerialize() {
		$Request = new Request();
		$Response = new Response();
		$items = [
			['title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'],
			['title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'],
		];
		$View = new AjaxView($Request, $Response);
		$View->set(['items' => $items, '_serialize' => ['items']]);
		$result = $View->render(false);

		$this->assertSame('application/json', $Response->type());
		$expected = ['error' => null, 'content' => null, 'items' => $items];
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * AjaxViewTest::testSerialize()
	 *
	 * @return void
	 */
	public function testRenderWithSerialize() {
		$Request = new Request();
		$Response = new Response();
		$items = [
			['title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'],
			['title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'],
		];
		$View = new AjaxView($Request, $Response);
		$View->set(['items' => $items, '_serialize' => 'items']);
		$View->viewPath = 'Items';
		$result = $View->render('index');

		$this->assertSame('application/json', $Response->type());
		$expected = ['error' => null, 'content' => 'My Index Test ctp', 'items' => $items];
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * AjaxViewTest::testError()
	 *
	 * @return void
	 */
	public function testError() {
		$Request = new Request();
		$Response = new Response();
		$items = [
			['title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'],
			['title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'],
		];
		$View = new AjaxView($Request, $Response);
		$View->set(['error' => 'Some message', 'items' => $items, '_serialize' => ['error', 'items']]);
		$View->viewPath = 'Items';
		$result = $View->render('index');

		$this->assertSame('application/json', $Response->type());
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
		$Request = new Request();
		$Response = new Response();
		$Controller = new Controller($Request, $Response);
		$View = new AjaxView($Request, $Response);
		$View->viewPath = 'Items';
		$View->subDir = false;
		$result = $View->render('index');

		$this->assertSame('application/json', $Response->type());
		$expected = ['error' => null, 'content' => 'My Index Test ctp'];
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
	}

	public function _testRender() {
		$Request = new Request();
		$Response = new Response();
		$Controller = new AjaxComponentTestController($Request, $Response);

		$Controller->viewBuilder()->className('Ajax.Ajax');
		$Controller->viewBuilder()->template('myTest');
		$Controller->viewBuilder()->templatePath('AjaxComponentTest');
		$Controller->myTest();

		//$Controller->subDir = false;
		$result = $Controller->render();

		$this->assertSame('application/json', $Response->type());
		$expected = ['error' => null, 'content' => 'My Index Test ctp'];
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
