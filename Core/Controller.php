<?php namespace Ditto\Core;
class Controller {
	protected static $buffer;

	public function __construct() {
		self::$buffer = ob_get_contents();
		ob_clean();
	}

	public static function getBuffer() {
		return self::$buffer;
	}

	protected function redirect($url) {
		header("Location: {$url}");
	}

	protected function respond($result = null, $extraData = null) {
		if (isset($result)) {
			if (is_bool($result)){
				$errors = self::$buffer . ob_get_clean();
				if (is_array($extraData)){
					$extraData['result'] = (int)$result;
					$extraData['errors'] = $errors;
					$returnJson = json_encode($extraData);
				}
				elseif (is_string($extraData))
					$returnJson = json_encode(array(
						'result' => (int)$result,
						'errors' => $errors,
						'message' => $extraData
					));
				else $returnJson = json_encode(array(
						'result' => (int)$result,
						'errors' => $errors
					));
			}
			else $returnJson = json_encode(array(
					'result' => 0
				));
			echo $returnJson;
			exit;
		}
		else exit (ob_get_clean());
	}
}