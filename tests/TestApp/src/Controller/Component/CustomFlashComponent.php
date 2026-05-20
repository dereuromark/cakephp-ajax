<?php
declare(strict_types=1);

namespace TestApp\Controller\Component;

use Cake\Controller\Component;

/**
 * Stand-in for a non-cake-core Flash component (e.g. dereuromark/cakephp-flash).
 * Extends Cake\Controller\Component directly — does NOT extend Cake\Controller\Component\FlashComponent.
 */
class CustomFlashComponent extends Component {

	/**
	 * @var array
	 */
	protected array $_defaultConfig = [
		'key' => 'flash',
	];

}
