<?php namespace Ditto\Core;
/*
 * Ditto
 *
 * Copyright 2013, Matthew Webb <matthewowebb@gmail.com>
 * Released under the MIT Licence
 * http://opensource.org/licenses/MIT
 *
 * Github:  http://github.com/mcwebb/ditto/
 * Version 0.3
 */
class Route {
	private $type;
	private $pattern;
	private $http_method;
	private $delegatee;
	private $controller;
	private $action;
	private $transliterates;

	public function __construct() { }

	public function pattern($pattern) {
		$this->pattern = '/^'. str_replace('/','\/',$pattern) .'$/';
		return $this;
	}

	public function on($http_method){
		$this->http_method = $http_method;
		return $this;
	}

	public function controller($controller){
		$this->controller = $controller;
		return $this;
	}

	public function action($action){
		$this->action = $action;
		$this->type = 'route';
		return $this;
	}

	public function transliterates($transliterates) {
		$this->transliterates = $transliterates;
		$this->type = 'autoroute';
		return $this;
	}

	public function bind() {
		Engine::load()->addRoute($this);
	}

	public function getType()	{ return $this->type; }
	public function getPattern()	{ return $this->pattern; }
	public function getHttpMethod() { return $this->http_method; }
	public function getDelegatee()	{ return $this->delegatee; }
	public function getController() {
		$controller = "controllers\\$this->controller";
		return new $controller;
	}
	public function getAction()		{ return $this->action; }
	public function getTransliterates($symbol = null) {
		if (is_null($symbol))
			return $this->transliterates;
		else
			return $this->transliterates[$symbol];
	}
}