<?php

class WellProtectedParentClass {
	private $privateParentProperty;

	public function __construct() {
		$this->privateParentProperty = 9000;
	}

	private function incrementPrivateParentPropertyValue() {
		$this->privateParentProperty++;
	}

	public function getPrivateParentProperty() {
		return $this->privateParentProperty;
	}
}

class WellProtectedClass extends WellProtectedParentClass {
	protected $property;
	private $privateProperty;

	public function __construct() {
		parent::__construct();
		$this->property = 1;
		$this->privateProperty = 42;
	}

	protected function incrementPropertyValue() {
		$this->property++;
	}

	private function incrementPrivatePropertyValue() {
		$this->privateProperty++;
	}

	public function getProperty() {
		return $this->property;
	}

	public function getPrivateProperty() {
		return $this->privateProperty;
	}

	protected function whatSecondArg( $a, $b = false ) {
		return $b;
	}
}
