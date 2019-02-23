<?php

namespace ash;

use eve\common\IHost;
use eve\common\factory\IBaseFactory;
use ash\token\IToken;



final class SolverFactory
implements ISolverFactory
{

	private $_baseFactory;
	private $_api;


	public function __construct(IBaseFactory $baseFactory, IHost $api) {
		$this->_baseFactory = $baseFactory;
		$this->_api = $api;
	}

	public function produce(IToken $root) : ISolver {
		return $this->_baseFactory->produce(Solver::class, [ $this->_api, $root ]);
	}
}
