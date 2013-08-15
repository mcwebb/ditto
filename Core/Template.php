<?php namespace Ditto\Core;
/*
 * Ditto
 *
 * Copyright 2013, Matthew Webb <matthewowebb@gmail.com>
 * Released under the MIT Licence
 * http://opensource.org/licenses/MIT
 *
 * Github:  http://github.com/mcwebb/ditto/
 * Version: 0.1
 */
class Template {
	public static $vars;
	public static $root;
	public static $root_abs;
	private $template_file;
	private $template_dir;
	private $template_name;
	public $script;

	public function __construct($template_file) {
		$this->template_file = $template_file;
		$this->template_dir = stristr($template_file, '/', true);
		$this->template_name = trim(
			stristr($template_file, '/'),
			'/'
		);
	}

	private function scripts() {
		$js = Engine::load()->getGlobalScripts();
		if (!is_string($this->script)) $js .= '';
		elseif (is_readable(
			Engine::root(true) ."scripts/{$this->script}.js"
		)) {
			ob_start();
			include Engine::root(true) ."scripts/{$this->script}.js";
			$js .= '<script type="text/javascript">'
				. ob_get_clean()
				.'</script>';
		}
		else $js .= '<!-- specified script unreadable -->';
		return $js;
	}

	private function head() {
		return Engine::load()->getGlobalStyles();
	}

	private function getBuffer() {
		return Controller::$buffer;
	}

	public function render(View $view = null) {
		self::$vars = get_object_vars($this);
		self::$root = Engine::root() .'templates/'
			.$this->template_dir .'/';
		self::$root_abs = Engine::root(true)
			.'templates/'. $this->template_dir .'/';
		ob_start();
		require self::$root_abs . $this->template_name .'.template.php';
		return ob_get_clean();
	}
}