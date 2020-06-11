<?php

namespace ash\token;


final class TernaryOperation
implements IBranchToken {

	private $_test;
	private $_default;
	private $_alternative;


	public function __construct(ITokenFactory $factory, array $tokens) {
		$this->_test = $tokens[0];
		$this->_default = $tokens[1];
		$this->_alternative = $tokens[2];
	}


	public function getType() : int {
		return IToken::TOKEN_TERNARY_OPERATION;
	}


	public function getChars() : string {
		return $this->_test->getChars() . '?' . $this->_default->getChars() . ':' . $this->_alternative->getChars();
	}

	public function getTest() : IToken {
		return $this->_test;
	}

	public function getNumBranches() : int {
		return 2;
	}

	public function getBranchAt(int $index) : IToken {
		switch ($index) {
			case 0 : return $this->_default;
			case 1 : return $this->_alternative;
			default : throw new \ErrorException();
		}
	}

	public function getProjection() : array {
		return [
			'type' => $this->getType(),
			'data' => [
				$this->_test->getProjection(),
				$this->_default->getProjection(),
				$this->_alternative->getProjection()
			]
		];
	}
}
