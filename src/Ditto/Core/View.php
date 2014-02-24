<?php namespace Ditto\Core;
/*
 * Ditto
 *
 * Copyright 2013, Matthew Webb <matthewowebb@gmail.com>
 * Released under the MIT Licence
 * http://opensource.org/licenses/MIT
 *
 * Github:  http://github.com/mcwebb/ditto/
 * Version 0.4
 */
class View {
	private $view_file;
	private $s;

	public function __construct($view_file, $s = ' ') {
		$this->view_file = $view_file;
		$this->s = $s;
	}

	public function render() {
		foreach (Template::$vars as $name => $value)
			$this->$name = $value;
		if (is_null($this->view_file))
			return $this->s;
		ob_start();
		require Engine::root(true)
			. 'views/'
			. $this->view_file .'.view.php';
		return ob_get_clean();
	}
}