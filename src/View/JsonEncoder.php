<?php

namespace Ajax\View;

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

		$error = null;
		if (json_last_error() !== JSON_ERROR_NONE) {
			$error = 'JSON encoding failed: ' . json_last_error_msg();
		}

		if ($result === false || $error) {
			throw new RuntimeException($error ?: 'JSON encoding failed');
		}

		return $result;
	}

}
