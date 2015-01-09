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

use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use Tools\View\AjaxView;


/**
 * AjaxViewTest
 *
 */
class AjaxViewTest extends TestCase {

	public $Ajax;

	/**
	 * AjaxViewTest::setUp()
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->Ajax = new AjaxView();

		App::build(array(
			'View' => array(Plugin::path('Tools') . 'Test' . DS . 'test_app' . DS . 'View' . DS)
		), App::RESET);
	}

	/**
	 * AjaxViewTest::testSerialize()
	 *
	 * @return void
	 */
	public function testSerialize() {
		$Request = new Request();
		$Response = new Response();
		$Controller = new Controller($Request, $Response);
		$items = array(
			array('title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'),
			array('title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'),
		);
		$Controller->set(array('items' => $items, '_serialize' => array('items')));
		$View = new AjaxView($Controller);
		$result = $View->render(false);

		$this->assertSame('application/json', $Response->type());
		$expected = array('error' => null, 'content' => null, 'items' => $items);
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
		$Controller = new Controller($Request, $Response);
		$items = array(
			array('title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'),
			array('title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'),
		);
		$Controller->set(array('items' => $items, '_serialize' => 'items'));
		$View = new AjaxView($Controller);
		$View->viewPath = 'Items';
		$result = $View->render('index');

		$this->assertSame('application/json', $Response->type());
		$expected = array('error' => null, 'content' => 'My Index Test ctp', 'items' => $items);
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
		$Controller = new Controller($Request, $Response);
		$items = array(
			array('title' => 'Title One', 'link' => 'http://example.org/one', 'author' => 'one@example.org', 'description' => 'Content one'),
			array('title' => 'Title Two', 'link' => 'http://example.org/two', 'author' => 'two@example.org', 'description' => 'Content two'),
		);
		$Controller->set(array('error' => 'Some message', 'items' => $items, '_serialize' => array('error', 'items')));
		$View = new AjaxView($Controller);
		$View->viewPath = 'Items';
		$result = $View->render('index');

		$this->assertSame('application/json', $Response->type());
		$expected = array('error' => 'Some message', 'content' => null, 'items' => $items);
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
		$View = new AjaxView($Controller);
		$View->viewPath = 'Items';
		$View->subDir = false;
		$result = $View->render('index');

		$this->assertSame('application/json', $Response->type());
		$expected = array('error' => null, 'content' => 'My Index Test ctp');
		$expected = json_encode($expected);
		$this->assertTextEquals($expected, $result);
	}

}
