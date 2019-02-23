<?php

declare(strict_types=1);
namespace test\api;

use PHPUnit\Framework\TestCase;
use ash\api;



final class FnOpsTest
extends TestCase
{

	private function _produceApi() : api\FnOps {
		return new api\FnOps();
	}


	public function testRunFnArray() {
		$api = $this->_produceApi();
		$method = $api->getMethodName('run', 'array');

		$val = $api->$method(function(float $a, float $b) { return $a + $b; }, [ 1.1, 1.1]);
		$this->assertEquals(2.2, $val);
	}
}
