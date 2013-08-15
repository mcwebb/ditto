<?php namespace Ditto\Core;
/*
 * Ditto
 *
 * Copyright 2013, Matthew Webb <matthewowebb@gmail.com>
 * Released under the MIT Licence
 * http://opensource.org/licenses/MIT
 *
 * Github:  http://github.com/mcwebb/ditto/
 * Version 0.2
 */
abstract class Bundle {
	protected $bundle_name;
	protected $bundle_type;
	static protected $root;
	static protected $root_abs;

	final public function __construct() {
		self::$root = Engine::root()
			. 'vendors/Ditto/'
			. $this->bundle_name
			. '/';
		self::$root_abs = Engine::root(true)
			. 'vendors/Ditto/'
			. $this->bundle_name
			. '/';
		$this->construct();
	}

	abstract public function construct();
}