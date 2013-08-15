<?php namespace Ditto\Core;
abstract class Module {
	protected $module_name;
	protected $module_type;
	static protected $root;
	static protected $root_abs;

	final public function __construct() {
		self::$root = Engine::root()
			. 'vendors/Ditto/'
			. $this->module_name
			. '/';
		self::$root_abs = Engine::root(true)
			. 'vendors/Ditto/'
			. $this->module_name
			. '/';
		$this->construct();
	}

	abstract public function construct();
}