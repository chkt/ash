<?php

namespace ash\api;

use eve\common\factory\IBaseFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\operator\IItemAccessorSurrogate;



final class ApiFactory
implements ISimpleFactory
{

	private $_base;
	private $_access;


	public function __construct(
		IBaseFactory $base,
		IItemAccessorSurrogate $access
	) {
		$this->_base = $base;
		$this->_access = $access;
	}


	public function produce(array& $config = []) {
		$base = $this->_base;
		$map = [
			'op-bool' => BoolOps::class,
			'op-int' => IntOps::class,
			'op-float' => FloatOps::class,
			'op-array' => ArrayOps::class,
			'op-fn' => FnOps::class
		];

		return $base->produce(ApiHost::class, [
			$base,
			$this->_access->produce($map)
		]);
	}
}
