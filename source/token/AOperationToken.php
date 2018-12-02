<?php

namespace ash\token;



abstract class AOperationToken
implements IOperationToken
{

	private $_op;


	public function __construct(ITokenFactory $factory, IOperatorToken $op) {
		$this->_op = $op;
	}


	abstract protected function _getOperands() : array;


	public function getChars() : string {
		$res = [ $this->_op->getChars() ];

		foreach ($this->_getOperands() as $operand) $res[] = $operand->getChars();

		return implode(' ', $res);
	}


	public function getOperator() : IToken {
		return $this->_op;
	}


	public function getNumOperands() : int {
		return count($this->_getOperands());
	}

	public function getOperandAt(int $index) : IToken {
		$ops = $this->_getOperands();
		$len = count($ops);

		if ($index < 0) $index = $len - $index;

		if ($index < 0 || $index >= $len) throw new \ErrorException();

		return $ops[$index];
	}


	public function getProjection() : array {
		$data = [ $this->_op->getProjection() ];

		foreach ($this->_getOperands() as $operand) $data[] = $operand->getProjection();

		return [
			'type' => $this->getType(),
			'data' => $data
		];
 	}
}
