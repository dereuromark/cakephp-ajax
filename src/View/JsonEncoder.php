<?php

namespace Ajax\View;

use RuntimeException;

class JsonEncoder {

	/**
	 * @param array<mixed> $dataToSerialize
	 * @param int $options
	 *
	 * @throws \RuntimeException
	 *
	 * @return string
	 */
	public static function encode(array $dataToSerialize, int $options = 0): string {
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
