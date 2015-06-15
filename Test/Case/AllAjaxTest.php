<?php
/**
 * Ajax Plugin - All plugin tests
 */
class AllAjaxTest extends PHPUnit_Framework_TestSuite {

	/**
	 * Suite method, defines tests for this suite.
	 *
	 * @return void
	 */
	public static function suite() {
		$Suite = new CakeTestSuite('All Ajax tests');

		$path = dirname(__FILE__);
		$Suite->addTestDirectory($path . DS . 'View');

		$path = dirname(__FILE__);
		$Suite->addTestDirectory($path . DS . 'Controller' . DS . 'Component');
		
		return $Suite;
	}

}
