<?php

namespace ash\api;

use eve\common\factory\IBaseFactory;
use eve\common\access\IItemAccessor;
use eve\common\assembly\AUniformHost;



final class ApiHost
extends AUniformHost
{

	private $_base;


	public function __construct(IBaseFactory $base, IItemAccessor $map) {

		parent::__construct($map);

		$this->_base = $base;
	}


	protected function _produceFromMap(IItemAccessor $map, string $key) {
		return $this->_base->produce($map->getItem($key));
	}
}
