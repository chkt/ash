<?php

namespace ash\token;

use eve\common\factory\IBaseFactory;



final class TokenFactory
implements ITokenFactory
{

	private $_base;


	public function __construct(IBaseFactory $base) {
		$this->_base = $base;
	}


	private function _getQName(string $name) {
		return '\\ash\\token\\' . ucfirst($name);
	}


	public function produce(string $name, array $args = []) : IToken {
		$qname = $this->_getQName($name);

		return $this->_base->produce($qname, [ $this, $args ]);
	}
}
