<?php

namespace TestApp\Controller;

use Cake\Controller\Controller;

class AjaxTestController extends Controller {

	/**
	 * @throws \Exception
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Ajax.Ajax');
	}

	/**
	 * A test action
	 *
	 * @return void
	 */
	public function myTest() {
	}

}
