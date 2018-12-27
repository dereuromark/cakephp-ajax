<?php

namespace Ajax\View;

use Cake\Core\Configure;
use Cake\Log\Log;
use RuntimeException;

class JsonEncoder {

	/**
	 * @param array $dataToSerialize
	 * @param int $options
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	public static function encode(array $dataToSerialize, $options = 0) {
		$result = json_encode($dataToSerialize, $options);
		if (json_last_error() !== JSON_ERROR_NONE) {
			$error = json_last_error_msg();
			if (!Configure::read('debug')) {
				Log::write('debug', $error);
				$error = 'JSON encoding failed';
			}
			$result = json_encode(['error' => $error], $options);
		}

		if ($result === false) {
			throw new RuntimeException('JSON encoding failed');
		}

		return $result;
	}

}
