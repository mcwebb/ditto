<?php namespace Ditto\Core;
class Engine {
	static private $instance;

	private $root;
	private $root_abs;
	private $errors = array();
	private $requestUrl;
	private $routes = array();
	private $handler404;
	private $environment;
	private $bundles = array();
	private $globalStyles = array();
	private $globalScripts = array();

	private function __construct($environment = 0, $root = null) {
		ob_start();
		$this->environment = $environment;
		if ($this->environment == 1){
			set_error_handler(array($this, 'error_handler'));
			ini_set('display_errors', 1); 
			error_reporting(E_ALL);
		}

		if (!isset($root))
			$this->root_abs = substr(__DIR__, 0, -19) .'/';
		else $this->root_abs = $_SERVER['DOCUMENT_ROOT'] . $root;

		$this->root = substr(
			$this->root_abs, strlen($_SERVER['DOCUMENT_ROOT'])
		);

		if (isset($_GET['url']))
			$this->requestUrl = trim($_GET['url'], '/');
		else $this->requestUrl = '';

		self::$instance = $this;
	}

	public function __destruct() {
		ob_end_flush();
	}

	public static function load($environment = 0, $root = null) {
		if (!empty(self::$instance))
			return self::$instance;
		else return new self ($environment, $root);
	}

	public static function root($abs = false) {
		if ($abs)
			return self::load()->root_abs;
		else return self::load()->root;
	}

	public static function getEnvironment() {
		return self::load()->environment;
	}

	public static function getErrors() { 
		return self::load()->errors;
	}

	public static function addGlobalScript($path) {
		return self::load()->globalScripts[] = $path;
	}

	public static function addGlobalStyle($path) {
		return self::load()->globalStyles[] = $path;
	}

	public function getGlobalScripts() {
		$s = "\n";
		if (self::getEnvironment() === 0) {
			$s .= '<script type="text/javascript">';
			foreach ($this->globalScripts as $path)
				$s .= "\n\n/*\n * $path\n */\n"
					. file_get_contents($_SERVER['DOCUMENT_ROOT'] . $path);
			$s .= "</script>\n";
		} else
			foreach ($this->globalScripts as $path)
				$s .= '<script type="text/javascript" src="'
					. $path .'"></script>'."\n";
		return $s;
	}

	public function getGlobalStyles() {
		$s = '';
		if (self::getEnvironment() === 0) {
			$s .= '<style>';
			foreach ($this->globalStyles as $path)
				$s .= "\n\n/*\n * $path\n */\n"
					. file_get_contents($_SERVER['DOCUMENT_ROOT'] . $path);
			$s .= "</style>\n";
		} else
			foreach ($this->globalStyles as $path)
				$s .= '<link rel="stylesheet" type="text/css" href="'
					. $path .'" />'."\n";
		return $s;
	}

	public function error_handler(
		$errno, $errstr, $errfile, $errline
	) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
			case E_STRICT:
				array_push($this->errors, array(
					'type' => 'info',
					'message' => $errstr,
					'file' => $errfile,
					'line' => $errline
				));
			break;

			case E_WARNING:
			case E_USER_WARNING:
				array_push($this->errors, array(
					'type' => 'warning',
					'message' => $errstr,
					'file' => $errfile,
					'line' => $errline
				));
			break;

			case E_ERROR:
			case E_USER_ERROR:
				array_push($this->errors, array(
					'type' => 'error',
					'message' => $errstr,
					'file' => $errfile,
					'line' => $errline
				));
				exit("FATAL error $errstr at $errfile:$errline");
			break;

			default:
				exit("Unknown error at $errfile:$errline");
		}
	}

	public function addRoute(Route $route) {
		$this->routes[] = $route;
	}

	public function dispatch($urlToMatch = null) {
		if (!isset($urlToMatch))
			$urlToMatch = $this->requestUrl;
		foreach ($this->routes as $key => $route) {
			// does the pattern matches the current route?
			if (!preg_match(
				$route->getPattern(),
				$urlToMatch,
				$url_variables
			)) continue;
			// get rid of the whole pattern match
			array_shift($url_variables);
			if ($_SERVER['REQUEST_METHOD'] == $route->getHttpMethod()) {
				call_user_func_array(
					array(
						$route->getController(),
						$route->getAction()
					), $url_variables
				);
				exit;
			}
			elseif (is_array($route->getTransliterates())
				or strlen($urlToMatch) < 1) {
				$transliteration = str_replace(
					'/',
					$route->getTransliterates('/'),
					$urlToMatch
				);
				// is this url variable?
				if (preg_match(
					'/^.+_{1}([0-9]+)$/',
					$transliteration,
					$url_variables
				)) {
					$transliteration = preg_replace(
						'/_{1}([0-9]+)$/', '', $transliteration
					);
					// get rid of the whole pattern match
					array_shift($url_variables);
				}
				$transliteration .= $route->getTransliterates(
					$_SERVER['REQUEST_METHOD']
				);
				if (strlen($this->requestUrl) === 0)
					$action = 'index';
				else {
					$action = substr($route->getPattern(), 2);
					$action = stristr($action, '(', true);
					$action = str_replace('\/', '_', $action);
					if (strlen($action) > 0)
						$action = str_replace(
							$action.'_',
							'',
							$transliteration
						);
					else $action = $transliteration;
				}
				if (is_callable(array(
					$route->getController(),
					$action
				))) {
					call_user_func_array(array(
						$route->getController(),
						$action
						), $url_variables
					);
					exit;
				}
			}
			elseif (is_object($route->getDelegatee())) {
				$pattern = substr($route->getPattern(), 2);
				$pattern = stristr($pattern, '(', true);
				$route->getDelegatee()->dispatch(
					substr(
						str_replace($pattern, '', $urlToMatch)
					, 1)
				);
			}
		}
		// the program has not exited thus there are no matching routes
		$this->throw404($urlToMatch);
	}

	private function throw404($url) {
		if (is_callable($this->handler404))
			$this->handler404($url);
		else {
			header('HTTP/1.0 404 Not Found');
			exit;
		}
	}

	public function set404Handler(callback $handler) {
		$this->handler404 = $handler;
	}

	public function bundle($bundle_name) {
		if (!isset($this->bundles[$bundle_name])) {
			$bundle_class = "\Ditto\\$bundle_name\\Bundle";
			$this->bundles[$bundle_name] = new $bundle_class;
		}
		return $this->bundles[$bundle_name];
	}

	public function route($pattern){
		$route = new Route;
		$route->pattern($pattern);
		return $route;
	}

	public function autoroute($pattern, $transliterates = array(
		'/' => '_',
		'GET' => '',
		'POST' => '_do',
		'DELETE' => '_delete',
		'PUT' => '_update'
	)) {
		$route = new Route ($this);
		$route
			->pattern(trim($pattern, '/') .'([a-zA-Z0-9/]*)')
			->transliterates($transliterates);
		return $route;
	}
}