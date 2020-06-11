<?php

namespace test\api;

use PHPUnit\Framework\TestCase;
use eve\common\factory\BaseFactory;
use eve\common\access\factory\TraversableAccessorFactory;
use ash\api;



final class ApiHostTest
extends TestCase
{

	private function _produceHost() : api\ApiHost {
		$base = new BaseFactory();
		$access = new TraversableAccessorFactory($base);
		$factory = new api\ApiFactory($base, $access);

		return $factory->produce();
	}

	public function testGetBoolApi() {
		$host = $this->_produceHost();
		$api = $host->getItem('op-bool');

		$this->assertInstanceOf(api\BoolOps::class, $api);
	}


	public function testGetIntApi() {
		$host = $this->_produceHost();
		$api = $host->getItem('op-int');

		$this->assertInstanceOf(api\IntOps::class, $api);
	}

	public function testGetFloatApi() {
		$host = $this->_produceHost();
		$api = $host->getItem('op-float');

		$this->assertInstanceOf(api\FloatOps::class, $api);
	}

	public function testGetStringApi() {
		$host = $this->_produceHost();
		$api = $host->getItem('op-string');

		$this->assertInstanceOf(api\StringOps::class, $api);
	}

	public function testGetArrayApi() {
		$host = $this->_produceHost();
		$api = $host->getItem('op-array');

		$this->assertInstanceOf(api\ArrayOps::class, $api);
	}

	public function testGetFnApi() {
		$host = $this->_produceHost();
		$api = $host->getItem('op-fn');

		$this->assertInstanceOf(api\FnOps::class, $api);
	}
}
