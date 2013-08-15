<?php namespace Ditto\Core;
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