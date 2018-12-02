<?php

namespace ash;

use eve\common\factory\IBaseFactory;
use ash\token\IToken;



final class SolverFactory
implements ISolverFactory
{

	private $_baseFactory;


	public function __construct(IBaseFactory $baseFactory) {
		$this->_baseFactory = $baseFactory;
	}

	public function produce(IToken $root) : ISolver {
		return $this->_baseFactory->produce(Solver::class, [ $root ]);
	}
}
